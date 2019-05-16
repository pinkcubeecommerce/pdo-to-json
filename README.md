# pdo-to-json
Format PDO query result into json output for the web

# Setup
1. Always specify the connection first
```php
use Pinkcube\PgToJson\Query;

Query::setConnection($pdo);
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
