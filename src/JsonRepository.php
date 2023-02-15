<?php

namespace Gasner\JsonRepository;

use Exception;
use JsonMachine\Exception\InvalidArgumentException;
use JsonMachine\Items;
use JsonMachine\JsonDecoder\ExtJsonDecoder;
use Ramsey\Uuid\Uuid;

/**
 * A class that provides a simple interface for storing and retrieving data in a JSON file.
 */
class JsonRepository
{

    /**
     * The path where JSON files are stored.
     * @var string
     */
    private static string $dataPath = './storage/';

    /**
     * The name of the table in the database.
     * @var string
     */
    private string $tableName;

    /**
     * An array of rows in the table.
     * @var array
     */
    private array $rows = [];

    /**
     * Constructs a new instance of the JsonRepository class.
     *
     * @param string $tableName The name of the table.
     * @throws InvalidArgumentException If the table name is not provided.
     */
    public function __construct(string $tableName)
    {
        $this->tableName = $tableName;
        $this->loadData();
    }

    /**
     * Retrieves selected columns from all records in the table.
     *
     * @param array $selectedColumns The names of the columns to include in the result.
     * @return array An array of records, each containing only the selected columns.
     */
    public function fetch($selectedColumns): array
    {
        $records = [];
        foreach ($this->rows as $uuid => $record) {
            $cleanedRecord = [];
            foreach ($selectedColumns as $column) {
                if (isset($record[$column])) {
                    $cleanedRecord[$column] = $record[$column];
                }
            }
            $records[$uuid] = $cleanedRecord;
        }

        return $records;
    }


    /**
     * Adds a new record to the table.
     *
     * @param array $recordData The data to be added as a new record.
     * @return static Returns the current instance of the JsonRepository class.
     * @throws InvalidArgumentException If the record data is not provided.
     */
    public function addRecord(array $recordData): static
    {
        $t = Uuid::uuid7();
        $this->rows[$t->toString()] = $recordData;
        $this->saveData();
        return $this;
    }

    /**
     * Gets a record from the table based on its UUID.
     *
     * @param mixed $uuid The UUID of the record to retrieve.
     * @return array|null Returns the data for the record, or null if it doesn't exist.
     */
    public function getRecord($uuid): ?array
    {
        $tableData = $this->loadData();
        return $tableData[$uuid] ??= null;
    }

    /**
     * Updates an existing record in the table.
     *
     * @param mixed $uuid The UUID of the record to update.
     * @param array $recordData The new data for the record.
     * @return static Returns the current instance of the JsonRepository class.
     */
    public function editRecord($uuid, $recordData): static
    {
        if (!isset($this->rows[$uuid])) {
            throw new Exception('record not found');
        }
        $this->rows[$uuid] = $recordData;
        $this->saveData();
        return $this;
    }

    /**
     * Deletes an existing record from the table.
     *
     * @param mixed $uuid The UUID of the record to delete.
     * @return static Returns the current instance of the JsonRepository class.
     */
    public function deleteRecord($uuid): static
    {
        unset($this->rows[$uuid]);
        $this->saveData();
        return $this;
    }

    /**
     * Gets the path to the table's JSON file.
     *
     * @return string Returns the path to the table's JSON file.
     */
    private function getTablePath(): string
    {
        return self::$dataPath . '/' . $this->tableName . '.json';
    }

    /**
     * Loads data from the table's JSON file.
     *
     * @param bool $refresh Whether to force a refresh of the data from the file.
     * @return array Returns an array of the data in the table.
     * @throws InvalidArgumentException If the table file cannot be read.
     */
    public function loadData(bool $refresh = false): array
    {
        if (empty($this->rows) || $refresh) {
            $this->rows = [];
            $items = Items::fromFile($this->getTablePath(), ['decoder' => new ExtJsonDecoder(true)]);
            foreach ($items as $index => $item) {
                $this->rows[$index] = $item;
            }
        }
        return $this->rows;
    }

    /**
     * Saves the current data in the repository to the file system.
     *
     * @return static
     */
    private function saveData(): static
    {
        $jsonData = json_encode($this->rows);
        file_put_contents($this->getTablePath(), $jsonData);
        return $this;
    }

    /**
     * Gets the path to the data directory used by the repository.
     *
     * @return string
     */
    public static function getDataPath(): string
    {
        return self::$dataPath;
    }

    /**
     * Sets the path to the data directory used by the repository.
     *
     * @param string $dataPath The new path to the data directory.
     */
    public static function setDataPath(string $dataPath): void
    {
        self::$dataPath = $dataPath;
    }

    /**
     * Gets the path to a specific table's JSON file based on the table name.
     *
     * @param string $tableName The name of the table.
     *
     * @return string The path to the table's JSON file.
     */
    private static function getPath($tableName): string
    {
        return self::$dataPath . '/' . $tableName . '.json';
    }

    /**
     *
     * Creates a new table with the given name, if it doesn't already exist, and returns a repository instance for it.
     *
     * @param string $tableName The name of the table to create.
     * @param boolean $destroyIfExist Whether to destroy the table if it already exists.
     *
     * @return JsonRepository A repository instance for the newly created table.
     * @throws InvalidArgumentException
     */
    public static function create(string $tableName, bool $destroyIfExist = false): JsonRepository
    {
        $tablePath = self::getPath($tableName);
        if (!file_exists($tablePath) || $destroyIfExist) {
            $jsonData = json_encode([]);
            file_put_contents($tablePath, $jsonData);
        }
        return new self($tableName);
    }

}
