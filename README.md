# JsonRepository

JsonRepository is a simple PHP class that provides basic CRUD operations for JSON files that act as a database.
Requirements

    PHP 8.0 or later

Installation

    Clone or download the repository
    Copy the JsonRepository.php file to your project directory

## Usage

```php
<?php
require_once './vendor/autoload.php';

use Gasner\JsonRepository\JsonRepository;

// Create a new instance of JsonRepository for a table named 'users'
$repository = new JsonRepository('users');

// Add a record to the 'users' table
$recordData = [
'name' => 'John Doe',
'email' => 'john.doe@example.com',
'age' => 35,
];
$repository->addRecord($recordData);

// Get a record from the 'users' table by UUID
$uuid = '518cffa8-522d-433f-9b87-1b055e8d964a';
$record = $repository->getRecord($uuid);

// Edit a record in the 'users' table
$uuid = '518cffa8-522d-433f-9b87-1b055e8d964a';
$recordData = [
'name' => 'Jane Doe',
'email' => 'jane.doe@example.com',
'age' => 36,
];
$repository->editRecord($uuid, $recordData);

// Delete a record from the 'users' table
$uuid = '518cffa8-522d-433f-9b87-1b055e8d964a';
$repository->deleteRecord($uuid);

// Fetch selected columns for all records in the 'users' table
$selectedColumns = ['name', 'email'];
$records = $repository->fetch($selectedColumns);
```

## Configuration

You can change the default data path by setting the JsonRepository::$dataPath property. By default, the data path is set to ./storage/.

```php
JsonRepository::setDataPath('/path/to/your/data/folder');
```

## Static methods

JsonRepository also provides static methods for creating new tables and setting the data path:

```php

// Create a new table named 'products'
JsonRepository::create('products');

// Get the current data path
$dataPath = JsonRepository::getDataPath();
```

