 [![Codacy Badge](https://api.codacy.com/project/badge/Grade/4753101a298943048849fd47c945704d)](https://www.codacy.com/manual/lucassouzavieira/arangodb-php-odm?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=lucassouzavieira/arangodb-php-odm&amp;utm_campaign=Badge_Grade)[![PHP](https://github.com/lucassouzavieira/arangodb-php-odm/actions/workflows/php.yml/badge.svg)](https://github.com/lucassouzavieira/arangodb-php-odm/actions/workflows/php.yml)

# ArangoDB PHP ODM

![ArangoDB](https://www.arangodb.com/wp-content/uploads/2016/05/ArangoDB_logo_@2.png)

## A new PHP driver for ArangoDB

## Installation

### Using composer
  - Run the command below on your project root.<br>
    `composer require lvieira/arangodb-php-odm`

## Usage

### Setting up a new connection

```php
use ArangoDB\Connection\Connection;

// Set a new Connection
$connection = new Connection([
    'endpoint' => 'http://yourarangohost:8529',
    'username' => 'YourUserName',
    'password' => 'YourSecretPasswd',
    'database' => 'YourDatabase',
]);

// Alternatively, you can set a host and a port to connect
$connection = new Connection([
    'host' => 'http://yourarangohost',
    'port' => 8529,
    'username' => 'YourUserName',
    'password' => 'YourSecretPasswd',
    'database' => 'YourDatabase',
]);

// Or set more custom options like 'connection' type and timeout
$connection = new Connection([
    'host' => 'http://yourarangohost',
    'port' => 8529,
    'username' => 'YourUserName',
    'password' => 'YourSecretPasswd',
    'database' => 'YourDatabase',
    'connection' => 'Keep-Alive',
    'timeout' => 30
]);
```

### Managing databases

With connection, you already set a database. You can get the database instance.

```php
use ArangoDB\Database\Database;
use ArangoDB\Collection\Collection;
use ArangoDB\Connection\Connection;

// Set up a connection
$connection = new Connection([
    'host' => 'http://yourarangohost',
    'port' => 8529,
    'username' => 'YourUserName',
    'password' => 'YourSecretPasswd',
    'database' => 'YourDatabase',
]);

$database = $connection->getDatabase();

// With a database object, we can retrieve information about it.
$infoAboutCurrentDatabase = $database->getInfo();

// Check if the database has a collection
if($database->hasCollection('my_collection_name')){
    echo "Collection exists!";
} else {
    echo "Collection doesn't exist"; 
}

// We can also create collections in the database
$collection = $database->createCollection('my_new_colletion');

// Or retrieve existing collections
$collection = $database->getCollection('my_existing_collection');

// With the Database class we can create and drop databases

// Lists the databases on the server
$dbList = Database::list($connection);

// You can create a new database using the existing connection
$result = Database::create($connection, 'my_database_name');

// And drop databases
$result = Database::drop($connection, 'db_to_drop');
```

### Collections

You can also work with collections objects directly.

```php
use ArangoDB\Collection\Collection;
use ArangoDB\Connection\Connection;

$connection = new Connection([
    'host' => 'http://yourarangohost',
    'port' => 8529,
    'username' => 'YourUserName',
    'password' => 'YourSecretPasswd',
    'database' => 'YourDatabase',
    'connection' => 'Keep-Alive',
    'timeout' => 30
]);

$database = $connection->getDatabase();

// If a collection exists on a database, the object will represent it.
$collection = new Collection('my_collection_name', $database);

// If the collection does not exist, you can create it with the method 'save'
$collection->save();

// Get all documents from the collection
foreach ($collection->all() as $document){
    // Do something.
}
```

### Documents

```php
use ArangoDB\Document\Document;
use ArangoDB\Connection\Connection;

// Set up a connection
$connection = new Connection([
    'host' => 'http://yourarangohost',
    'port' => 8529,
    'username' => 'YourUserName',
    'password' => 'YourSecretPasswd',
    'database' => 'YourDatabase',
]);

// Check if the database has a collection called 'awesome_bands'. If not, create it.
$db = $connection->getDatabase();
if (!$db->hasCollection('awesome_bands')) {
    $db->createCollection('awesome_bands');
}

// Get the collection object
$collection = $db->getCollection('awesome_bands');

$documentAttributes = [
    'band' => 'Quiet Riot',
    'members' => [
        'Kevin DuBrow',
        'Rudy Sarzo',
        'Carlos Cavazo',
        'Frankie Banali'
    ],
    'active_since' => 1975,
    'city' => 'Los Angeles',
    'country' => 'USA'
];

// Create the document object
$document = new Document($documentAttributes, $collection);

// Save the document on collection.
$document->save();

// Add or change document attributes to update the document
$document->status = 'active';
$document->save(); // Will update your document on server;

// Delete the document from the collection.
$document->delete();
```

### Transactions

You can perform transactions on ArangoDB Server sending JavaScript code.

```php
use ArangoDB\Exceptions\TransactionException;
use ArangoDB\Transaction\JavascriptTransaction;

// Define collections to perform write and read operations.
$options = [
    'collections' => [
        'read' => [
            'fighter_jets'
        ],
        'write' => [
            'fighter_jets'
        ]
    ]
];

// Your JS action to execute.
$action = "function(){ var db = require('@arangodb').db; db.fighter_jets.save({});  return db.fighter_jets.count(); }";

try {
    $transaction = new JavascriptTransaction($this->getConnectionObject(), $action, $options);
    $result = $transaction->execute(); // Will return 1.
} catch (TransactionException $transactionException) {
    // Throws a TransactionException in case of error.
    return $transactionException->getMessage();
}
```

You can also perform transactions directly from PHP.

```php
use ArangoDB\Document\Document;
use ArangoDB\Connection\Connection;
use ArangoDB\Transaction\StreamTransaction as Transaction;

$connection = new Connection([
    'host' => 'http://yourarangohost',
    'port' => 8529,
    'username' => 'YourUserName',
    'password' => 'YourSecretPasswd',
    'database' => 'YourDatabase',
]);

$db = $connection->getDatabase();
if (!$db->hasCollection('fighter_jets')) {
    $db->createCollection('fighter_jets');
}

// Define collections to perform write and read operations.
$options = [
    'collections' => [
        'write' => [
            'fighter_jets'
        ]
    ]
];

// Declare a new transaction.
$transaction = new Transaction($connection, $options);

try {
    // Start transaction.
    $transaction->begin();

    // Do something
    $collection = $db->getCollection('fighter_jets');
    $viper = new Document(['model' => 'F-16 Viper', 'status' => 'In service', 'origin' => 'USA'], $collection);
    $gripen = new Document(['model' => 'JAS 39 Gripen', 'status' => 'In service', 'origin' => 'Sweden'], $collection);

    $viper->save();
    $gripen->save();

    // Commit the operations.
    $transaction->commit();
} catch (\Exception $exception) {
    // Some error occurred. Abort transaction.
    $transaction->abort();
}
```

## Documentation

Check the [API Reference](https://lucassouzavieira.github.io/arangodb-php-odm/).

## Contributing

[Check how to contribute to this project](CONTRIBUTING.md)
