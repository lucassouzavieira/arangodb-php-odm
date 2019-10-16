# ArangoDB PHP ODM
![ArangoDB](https://www.arangodb.com/wp-content/uploads/2016/05/ArangoDB_logo_@2.png)

### A new PHP driver for ArangoDB

## Installation

## Usage
#### Setting up a new connection
```php
use ArangoDB\Connection\Connection;

// Set a new Connection
$connection = new Connection([
    'endpoint' => 'ssl://myarangohost:8529',
    'username' => 'YourUserName',
    'password' => 'YourSecretPasswd',
    'database' => 'YourDatabase',
]);

// Alternatively, you can set an host and a port to connect
$connection = new Connection([
    'endpoint' => 'http://myarangohost',
    'port' => 8529,
    'username' => 'YourUserName',
    'password' => 'YourSecretPasswd',
    'database' => 'YourDatabase',
]);

// Or set more custom options like 'connection' type and timeout
$connection = new Connection([
    'endpoint' => 'http://myarangohost',
    'port' => 8529,
    'username' => 'YourUserName',
    'password' => 'YourSecretPasswd',
    'database' => 'YourDatabase',
    'connection' => 'Keep-Alive',
    'timeout' => 30
]);
```

#### Managing databases
With connection, you already set an Database. You can get the database instance.
```php
$database = $connection->getDatabase();

// With database object, we can retrive informations about it.
$infoAboutCurrentDatabase = $database->getInfo();

// Check if database has a collection
if($database->hasCollection('my_collection_name')){
    echo "Collection exists!";
} else {
    echo "Collection doesn't exists"; 
}

// We can also create collections in database
$collection = $database->createCollection('my_new_colletion');

// Or retrieve existing collections
$collection = $database->getCollection('my_existing_collection');
```
## Documentation

## Contributing
[Check how contribute in this project](CONTRIBUTING.md)
