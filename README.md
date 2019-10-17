# ArangoDB PHP ODM
![ArangoDB](https://www.arangodb.com/wp-content/uploads/2016/05/ArangoDB_logo_@2.png)

### A new PHP driver for ArangoDB

## Installation
#### Using composer
Run the command bellow on your project root.  
`composer require lvieira/arangodb-php-odm`

## Usage
#### Setting up a new connection
```php
use ArangoDB\Connection\Connection;

// Set a new Connection
$connection = new Connection([
    'endpoint' => 'http://myarangohost:8529',
    'username' => 'YourUserName',
    'password' => 'YourSecretPasswd',
    'database' => 'YourDatabase',
]);

// Alternatively, you can set an host and a port to connect
$connection = new Connection([
    'host' => 'http://myarangohost',
    'port' => 8529,
    'username' => 'YourUserName',
    'password' => 'YourSecretPasswd',
    'database' => 'YourDatabase',
]);

// Or set more custom options like 'connection' type and timeout
$connection = new Connection([
    'host' => 'http://myarangohost',
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
use ArangoDB\Database\Database;
use ArangoDB\Collection\Collection;
use ArangoDB\Connection\Connection;

// Set up a connection
$connection = new Connection([
    'host' => 'http://myarangohost',
    'port' => 8529,
    'username' => 'YourUserName',
    'password' => 'YourSecretPasswd',
    'database' => 'YourDatabase',
]);

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

// With Database class we can create and drop databases

// Lists the databases on server
$dbList = Database::list($connection);

// You can create a new database using the existing connection
$result = Database::create($connection, 'my_database_name');

// And drop databases
$result = Database::drop($connection, 'db_to_drop');
```

#### Collections
You can also work with collections objects directly.

```php
use ArangoDB\Collection\Collection;
use ArangoDB\Connection\Connection;

$connection = new Connection([
    'host' => 'http://myarangohost',
    'port' => 8529,
    'username' => 'YourUserName',
    'password' => 'YourSecretPasswd',
    'database' => 'YourDatabase',
    'connection' => 'Keep-Alive',
    'timeout' => 30
]);

$database = $connection->getDatabase();

// If collection exists on database, the object will be a representation of it.
$collection = new Collection('my_collection_name', $database);

// If collection not exists, you can create it with method 'save'
$collection->save();
```
## Documentation

## Contributing
[Check how contribute in this project](CONTRIBUTING.md)
