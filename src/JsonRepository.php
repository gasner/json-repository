<?php

namespace Elad\FlashyJson;

use JsonMachine\Exception\InvalidArgumentException;
use JsonMachine\Items;
use JsonMachine\JsonDecoder\ExtJsonDecoder;
use Ramsey\Uuid\Uuid;

class JsonRepository
{

    private static string $dataPath = './storage/';
    private string $tableName;

    private array $rows = [];

    public function __construct(string $tableName)
    {
        $this->tableName = $tableName;
        $this->loadData();
    }


    /**
     * @throws InvalidArgumentException
     */
    public function addRecord(array $recordData): static
    {

        $t = Uuid::uuid7();

        $this->rows[$t->toString()] = $recordData;
        $this->saveData();
        return $this;
    }

    public function getRecord($uuid):?array
    {
        $tableData = $this->loadData();
        return $tableData[$uuid] ??= null;
    }

    public function editRecord($uuid, $recordData): static
    {
        $this->rows[$uuid] = $recordData;
        $this->saveData();
        return $this;
    }

    public function deleteRecord($uuid): static
    {

        unset($this->rows[$uuid]);
        $this->saveData();
        return $this;
    }


    private function getTablePath(): string
    {
        return self::$dataPath . '/' . $this->tableName . '.json';
    }


    /**
     * @throws InvalidArgumentException
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

    private function saveData(): static
    {
        $jsonData = json_encode($this->rows);
        file_put_contents($this->getTablePath(), $jsonData);
        return $this;
    }

    /**
     * @return string
     */
    public static function getDataPath(): string
    {
        return self::$dataPath;
    }

    /**
     * @param string $dataPath
     */
    public static function setDataPath(string $dataPath): void
    {
        self::$dataPath = $dataPath;
    }

    private static function getPath($tableName): string
    {
        return self::$dataPath . '/' . $tableName . '.json';
    }

    /**
     * @param $tableName
     * @return JsonRepository
     */
    public static function create($tableName): JsonRepository
    {
        $tablePath = self::getPath($tableName);
        $jsonData = json_encode([]);
        file_put_contents($tablePath, $jsonData);
        return new self($tableName);
    }

}
