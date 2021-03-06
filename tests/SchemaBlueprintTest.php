<?php

namespace Culpa\Tests;

use Culpa\Database\Schema\Blueprint;
use Culpa\Tests\Bootstrap\CulpaTest;
use Illuminate\Database\Schema\Builder;

/**
 * Due to protected methods called from the constructor, i was having trouble to test the actual booting of the Trait.
 * TODO: test a foreign key constraint, cant get this to work in SQLite
 */
class SchemaBlueprintTest extends CulpaTest
{
    /**
     * @var string
     */
    private $tableName = 'testTable';

    /**
     * @var string
     */
    protected $columnName = 'column1';

    /**
     * @var Builder
     */
    protected $schemaBuilder;

    /**
     * Overwriting the facade is kinda hard in the application factory
     * So setup the resolver here.
     */
    public function setUp()
    {
        $schemabuilder = static::$app->make('db')->connection()->getSchemabuilder();
        $schemabuilder->blueprintResolver(function ($table, $callback) {
            return new Blueprint($table, $callback);
        });

        $this->schemaBuilder = $schemabuilder;
    }

    /**
     * The CreatedBy method in the blueprint class should create a new created_by column
     */
    public function testCreatedBy()
    {
        $this->schemaBuilder->create($this->tableName, function (Blueprint $table) {
            $table->createdBy();
        });

        $this->assertTrue($this->schemaBuilder->hasColumn($this->tableName, 'created_by'));
    }

    /**
     * The CreatedBy method in the blueprint class should create a new created_by column that is nullable
     */
    public function testCreatedByNullable()
    {
        $columnName = $this->columnName;
        $this->schemaBuilder->create($this->tableName, function (Blueprint $table) use ($columnName) {
            $table->text($columnName);
            $table->createdBy(true);
        });

        $this->assertTrue($this->schemaBuilder->hasColumn($this->tableName, $columnName));
        $this->assertTrue($this->schemaBuilder->hasColumn($this->tableName, 'created_by'));

        static::$app->make('db')->connection()->insert(
            'INSERT INTO ' . $this->tableName . '(' . $columnName . ') VALUES ("testing feature...")'
        );
        $results = static::$app->make('db')->connection()->select('SELECT * FROM ' . $this->tableName);
        $this->assertEquals(1, count($results));
    }

    /**
     * The updatedBy method in the blueprint class should create a new updated_by column
     */
    public function testUpdatedBy()
    {
        $this->schemaBuilder->create($this->tableName, function (Blueprint $table) {
            $table->updatedBy();
        });

        $this->assertTrue($this->schemaBuilder->hasColumn($this->tableName, 'updated_by'));
    }


    /**
     * The updatedBy method in the blueprint class should create a new updated_by column that is nullable
     */
    public function testUpdatedByNullable()
    {
        $columnName = $this->columnName;
        $this->schemaBuilder->create($this->tableName, function (Blueprint $table) use ($columnName) {
            $table->text($columnName);
            $table->updatedBy(true);
        });

        $this->assertTrue($this->schemaBuilder->hasColumn($this->tableName, $columnName));
        $this->assertTrue($this->schemaBuilder->hasColumn($this->tableName, 'updated_by'));

        static::$app->make('db')->connection()->insert(
            'INSERT INTO ' . $this->tableName . '(' . $columnName . ') VALUES ("testing feature...")'
        );

        $results = static::$app->make('db')->connection()->select('SELECT * FROM ' . $this->tableName);
        $this->assertEquals(1, count($results));
    }

    /**
     * The deletedBy method in the blueprint class should create a new deleted_by column
     */
    public function testDeletedBy()
    {
        $this->schemaBuilder->create($this->tableName, function (Blueprint $table) {
            $table->deletedBy();
        });

        $this->assertTrue($this->schemaBuilder->hasColumn($this->tableName, 'deleted_by'));
    }

    /**
     * The deletedBy method in the blueprint class should create a new deleted_by column that is nullable
     */
    public function testDeletedByNullable()
    {
        $columnName = $this->columnName;
        $this->schemaBuilder->create($this->tableName, function (Blueprint $table) use ($columnName) {
            $table->text($columnName);
            $table->deletedBy(true);
        });

        $this->assertTrue($this->schemaBuilder->hasColumn($this->tableName, $columnName));
        $this->assertTrue($this->schemaBuilder->hasColumn($this->tableName, 'deleted_by'));

        static::$app->make('db')->connection()->insert(
            'INSERT INTO ' . $this->tableName . '(' . $columnName . ') VALUES ("testing feature...")'
        );

        $results = static::$app->make('db')->connection()->select('SELECT * FROM ' . $this->tableName);
        $this->assertEquals(1, count($results));
    }

    /**
     * The blameable method in the blueprint table should create all three fields
     */
    public function testBleamable()
    {
        $this->schemaBuilder->create($this->tableName, function (Blueprint $table) {
            $table->blameable();
        });

        $this->assertTrue($this->schemaBuilder->hasColumn($this->tableName, 'created_by'));
        $this->assertTrue($this->schemaBuilder->hasColumn($this->tableName, 'updated_by'));
        $this->assertTrue($this->schemaBuilder->hasColumn($this->tableName, 'deleted_by'));
    }

    /**
     * In addition to the testBleamble() test, you can configure what fields you want generated.
     */
    public function testBlameableConfigurable()
    {
        $this->schemaBuilder->create($this->tableName, function (Blueprint $table) {
            $table->blameable(['created']);
        });

        $this->assertTrue($this->schemaBuilder->hasColumn($this->tableName, 'created_by'));
        $this->assertFalse($this->schemaBuilder->hasColumn($this->tableName, 'updated_by'));
        $this->assertFalse($this->schemaBuilder->hasColumn($this->tableName, 'deleted_by'));

        $this->schemaBuilder->drop($this->tableName);
        $this->schemaBuilder->create($this->tableName, function (Blueprint $table) {
            $table->blameable(['created', 'updated']);
        });

        $this->assertTrue($this->schemaBuilder->hasColumn($this->tableName, 'created_by'));
        $this->assertTrue($this->schemaBuilder->hasColumn($this->tableName, 'updated_by'));
        $this->assertFalse($this->schemaBuilder->hasColumn($this->tableName, 'deleted_by'));

        $this->schemaBuilder->drop($this->tableName);
        $this->schemaBuilder->create($this->tableName, function (Blueprint $table) {
            $table->blameable(['deleted', 'updated']);
        });

        $this->assertFalse($this->schemaBuilder->hasColumn($this->tableName, 'created_by'));
        $this->assertTrue($this->schemaBuilder->hasColumn($this->tableName, 'updated_by'));
        $this->assertTrue($this->schemaBuilder->hasColumn($this->tableName, 'deleted_by'));
    }

    public function tearDown()
    {
        $this->schemaBuilder->drop($this->tableName);
    }
}
