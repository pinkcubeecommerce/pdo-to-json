<?php

namespace Pinkcube\PdoToJson;

use PDO;
use Exception;

class Query
{
    /**
     * Location from the config file.
     *
     * @var string
     */
    protected $configFile = 'pdo-to-json.config.php';

    /**
     * Contains the pdo connection to the database.
     *
     * @var PDO
     */
    protected static $pdo = null;

    /**
     * Contains the statement for execution.
     *
     * @var PDOStatement
     */
    protected $query = null;

    /**
     * Contains the raw query as a string
     *
     * @var string
     */
    protected $rawQuery = null;

    /**
     * Contains the raw query result.
     *
     * @var array
     */
    protected $rawResult = null;

    /**
     * Contains the processed query result.
     *
     * @var array
     */
    protected $result = null;

    /**
     * Create a new query instance.
     *
     * @param  string  $query
     * @param  array|callback  $data
     * @param callback  $callback
     * @return void
     */
    public function __construct($query, $data = null, $callback = null)
    {
        if (! static::$pdo) {
            $this->resolveFromConfigFile();
        }

        $this->checkIfPdoConnectionIsSet();

        $this->rawQuery = $query;

        if (is_callable($data)) {
            $callback = $data;
            $data = null;
        }

        $this->query = static::$pdo->prepare($query);
        $this->query->execute($data);

        if (is_callable($callback)) {
            $this->process($callback);
        }
    }

    /**
     * Set the connection for the query class.
     *
     * @param PDO  $pdo
     * @return void
     */
    public static function setConnection($pdo)
    {
        static::$pdo = $pdo;
    }

    /**
     * Process the query result.
     *
     * @param callback  $callback
     * @return static
     */
    public function process($callback)
    {
        $this->result = $callback($this->result());

        return $this;
    }

    /**
     * Retrieve the processed query result,
     * when no processed query result was found
     * it will return the raw query result.
     *
     * @return array
     */
    public function result()
    {
        if ($this->result) {
            return $this->result;
        }

        return $this->rawResult();
    }

    /**
     * Retrieve the raw query result.
     *
     * @return array
     */
    public function rawResult()
    {
        if ($this->rawResult) {
            return $this->rawResult;
        }

        return $this->rawResult = $this->query->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Output the query result as json.
     *
     * @return array
     */
    public function outputAsJson()
    {
        header('Content-type: application/json');
        echo json_encode($this->result());
    }

    /**
     * Resolve and parse the configuration file,
     * use the connection string to create a new PDO connection.
     * And set the connection as the current connection.
     *
     * @return void
     */
    protected function resolveFromConfigFile()
    {
        $config = null;

        if (file_exists($this->configFile)) {
            $config = require_once($this->configFile);
        }

        if ($config && $connectionString = $config['connection_string']) {
            static::setConnection(new PDO($connectionString));
        }
    }

    /**
     * Check if there is a current connection stored.
     *
     * @return void
     */
    protected function checkIfPdoConnectionIsSet()
    {
        if (! static::$pdo) {
            throw new Exception('Please specify the proper PDO connection first.');
        }
    }
}

