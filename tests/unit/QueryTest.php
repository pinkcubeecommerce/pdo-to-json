<?php

namespace Tests\Unit;

use PDO;
use Pinkcube\PgToJson\Query;
use PHPUnit\Framework\TestCase;

class QueryTest extends TestCase
{
    protected $pdo = null;

    protected $users = [
        ['id' => 1, 'firstname' => 'John', 'lastname' => 'Doe', 'age' => 30],
        ['id' => 2, 'firstname' => 'Jane', 'lastname' => 'Doe', 'age' => 22],
        ['id' => 3, 'firstname' => 'Mark', 'lastname' => 'Foo', 'age' => 20],
    ];

    protected function migrateDatabase()
    {
        $this->pdo->exec("CREATE table users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            firstname tex tNOT NULL,
            lastname text NOT NULL,
            age int NOT NULL);"
        );

        foreach ($this->users as $user) {
            unset($user['id']);
            $this->pdo->prepare("insert into users (firstname, lastname, age) values (:firstname, :lastname, :age)")->execute($user);
        }
    }

    public function setUp() : void
    {
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
        $this->migrateDatabase();

        Query::setConnection($this->pdo);

    }

    /** @test */
    public function it_is_possible_to_make_a_raw_query()
    {
        $query = new Query('select * from users');

        $this->assertEquals($this->users, $query->rawResult());
    }

    /** @test */
    public function it_is_possible_to_process_the_query_result()
    {
        $query = new Query('select * from users');

        $query->process(function ($users) {
            return array_map(function ($user) {
                $user['fullname'] = "{$user['firstname']} {$user['lastname']}";

                return $user;
            }, $users);
        });

        $this->assertEquals($this->users, $query->rawResult());

        $usersWithFullName = array_map(function ($user) {
            $user['fullname'] = "{$user['firstname']} {$user['lastname']}";

            return $user;
        }, $this->users);
        $this->assertEquals($usersWithFullName, $query->result());
    }

    /** @test */
    public function it_is_possible_to_process_the_result_directly()
    {
        $query = new Query('select * from users', function ($users) {
            return array_map(function ($user) {
                $user['fullname'] = "{$user['firstname']} {$user['lastname']}";

                return $user;
            }, $users);
        });

        $usersWithFullName = array_map(function ($user) {
            $user['fullname'] = "{$user['firstname']} {$user['lastname']}";

            return $user;
        }, $this->users);

        $this->assertEquals($usersWithFullName, $query->result());
    }

    /** @test */
    public function it_is_possible_to_chain_process_calls()
    {
        $query = new Query('select * from users');

        $query->process(function ($users) {
            return array_map(function ($user) {
                $user['fullname'] = "{$user['firstname']} {$user['lastname']}";

                return $user;
            }, $users);
        })->process(function ($users) {
            return array_map(function ($user) {
                $user['fullname_and_age'] = "{$user['fullname']}:{$user['age']}";

                return $user;
            }, $users);
        });

        $usersWithFullName = array_map(function ($user) {
            $user['fullname'] = "{$user['firstname']} {$user['lastname']}";
            $user['fullname_and_age'] = "{$user['firstname']} {$user['lastname']}:{$user['age']}";

            return $user;
        }, $this->users);

        $this->assertEquals($usersWithFullName, $query->result());
    }

    /** @test */
    public function when_no_process_result_isset_the_process_result_will_return_the_raw_result()
    {
        $query = new Query('select * from users');

        $this->assertEquals($this->users, $query->result());
    }


    /**
     * @test
     * @runInSeparateProcess
     * */
    public function it_is_possible_to_output_the_result_as_json()
    {
        $query = new Query('select * from users');

        ob_start();
        $query->outputAsJson();
        $output = ob_get_contents();

        ob_end_clean();

        $this->assertEquals(
            json_encode($query->result()),
            $output
        );
    }
}
