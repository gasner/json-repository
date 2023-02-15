<?php

use Gasner\JsonRepository\JsonRepository;
use PHPUnit\Framework\TestCase;

class JsonRepositoryTest extends TestCase
{
    private $tableName = 'test_table';

    public function setUp(): void
    {
        parent::setUp();

        // Set the data path to a temporary directory
        JsonRepository::setDataPath(sys_get_temp_dir());

        // Create the test table
        JsonRepository::create($this->tableName);
    }

    public function tearDown(): void
    {
        // Remove the test table
        $tablePath = JsonRepository::getDataPath() . '/' . $this->tableName . '.json';
        unlink($tablePath);

        parent::tearDown();
    }

    public function testAddRecord()
    {
        $repo = new JsonRepository($this->tableName);
        $recordData = ['name' => 'John', 'age' => 30];
        $repo->addRecord($recordData);

        $this->assertCount(1, $repo->loadData());
    }

    public function testGetRecord()
    {
        $repo = new JsonRepository($this->tableName);
        $recordData = ['name' => 'John', 'age' => 30];
        $repo->addRecord($recordData);
        $uuid = array_key_first($repo->loadData());

        $this->assertEquals($recordData, $repo->getRecord($uuid));
    }

    public function testEditRecord()
    {
        $repo = new JsonRepository($this->tableName);
        $recordData = ['name' => 'John', 'age' => 30];
        $repo->addRecord($recordData);
        $uuid = array_key_first($repo->loadData());

        $newRecordData = ['name' => 'Bob', 'age' => 35];
        $repo->editRecord($uuid, $newRecordData);

        $this->assertEquals($newRecordData, $repo->getRecord($uuid));
    }

    public function testEditRecordThrowsExceptionIfRecordNotFound()
    {
        $repo = new JsonRepository($this->tableName);

        $recordData = [
            'name' => 'John Doe',
            'email' => 'johndoe@example.com',
            'age' => 30,
        ];

        // Act
        $this->expectException(Exception::class);
        $repo->editRecord('nonexistent_uuid', $recordData);
    }

    public function testDeleteRecord()
    {
        $repo = new JsonRepository($this->tableName);
        $recordData = ['name' => 'John', 'age' => 30];
        $repo->addRecord($recordData);
        $uuid = array_key_first($repo->loadData());

        $repo->deleteRecord($uuid);
        $repo->loadData(true);

        $this->assertNull($repo->getRecord($uuid));
    }


    public function testFetch()
    {
        // Create an instance of the JsonRepository class
        $repo = new JsonRepository($this->tableName);

        // Add some test data to the repository
        $repo->addRecord([
            'name' => 'Alice',
            'email' => 'alice@example.com',
            'age' => 30,
            'gender' => 'female',
        ]);

        $repo->addRecord([
            'name' => 'Bob',
            'email' => 'bob@example.com',
            'age' => 35,
            'gender' => 'male',
        ]);

        $repo->addRecord([
            'name' => 'Charlie',
            'email' => 'charlie@example.com',
            'age' => 25,
            'gender' => 'male',
        ]);

        // Call the fetch function to retrieve only the 'name' and 'email' columns
        $selectedColumns = ['name', 'email'];
        $result = $repo->fetch($selectedColumns);

        // Assert that the returned data has the correct format
        $this->assertIsArray($result);
        $this->assertCount(3, $result);

        // Assert that the returned data has only the selected columns
        foreach ($result as $record) {
            $this->assertArrayHasKey('name', $record);
            $this->assertArrayHasKey('email', $record);
            $this->assertArrayNotHasKey('age', $record);
            $this->assertArrayNotHasKey('gender', $record);
        }
    }


}
