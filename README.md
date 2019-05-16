# pdo-to-json
Format PDO query result into json output for the web

# Setup
Always specify the connection first

## Use setConnection method
```php
use Pinkcube\PdoToJson\Query;

Query::setConnection($pdo);
```
## Use a config file
Create the config file within your project root: `pdo-to-json.config.php`,
add the following content and update `your-connection-string` with yours:
```php
<?php

return [
    'connection_string' => 'your-connection-string'
];
```

# Basic usage

1. Specify the query
```php
$query = new Query('select * from users');
```

2. Fetch results as an array
```php
$result = $query->result();
```

3. Output the result as json
```php
$result = $query->outputAsJson();
```

# Process the result

1. Specify the query
```php
$query = new Query('select * from users');
```

2. Process the results
```php
$query->process(function ($users) {
    return array_map(function ($user) {
        $user['fullname'] = "{$user['firstname']} {$user['lastname']}";
        return $user;
    }, $users);
});
```
3. Fetch results as an array

```php
$result = $query->result();
```

To fetch the raw (origin) results:
```php
$result = $query->rawResult();
```

# Process chaining
```php
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
```
