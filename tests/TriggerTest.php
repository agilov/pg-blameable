<?php

namespace agilov\pgtimestamp\tests;

use PHPUnit\Framework\TestCase;

/**
 * Class TriggerTest
 * vendor/bin/phpunit tests/TriggerTest
 *
 * @author Roman Agilov <agilovr@gmail.com>
 */
final class TriggerTest extends TestCase
{
    /** @var \PDO */
    protected $pg;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $this->pg = new \PDO('pgsql:dbname=test', 'postgres');

        $this->pg->exec('DROP TABLE test');
        $this->pg->exec('
CREATE TABLE test (
  content TEXT,
  created_by VARCHAR(64),
  updated_by VARCHAR(64)
);');
    }

    /**
     * vendor/bin/phpunit --filter testCreatedBy tests/TriggerTest
     */
    public function testCreatedBy()
    {
        $this->pg->exec("select attach_blameable_behavior('test', 'created_by', 'INSERT');");
        $this->pg->exec("INSERT INTO test (content) VALUES ('test');");
        $raws = $this->pg->query("SELECT * FROM test", \PDO::FETCH_ASSOC)->fetchAll();
        $this->assertEquals($raws[0]['created_by'], 'postgres');
    }

    /**
     * vendor/bin/phpunit --filter testUpdatedBy tests/TriggerTest
     */
    public function testUpdatedBy()
    {
        $this->pg->exec("select attach_blameable_behavior('test', 'updated_by', 'INSERT OR UPDATE');");
        $this->pg->exec("INSERT INTO test (content) VALUES ('test');");
        $this->pg->exec("UPDATE test SET content = 'test'");

        $raws = $this->pg->query("SELECT * FROM test", \PDO::FETCH_ASSOC)->fetchAll();
        $this->assertEquals($raws[0]['updated_by'], 'postgres');
    }

    /**
     * vendor/bin/phpunit --filter testUpdatedBy tests/TriggerTest
     */
    public function testCombine()
    {
        $this->pg->exec("select attach_blameable_behavior('test', 'created_by', 'INSERT');");
        $this->pg->exec("select attach_blameable_behavior('test', 'updated_by', 'INSERT OR UPDATE');");
        $this->pg->exec("INSERT INTO test (content) VALUES ('test');");
        $this->pg->exec("UPDATE test SET content = 'test'");

        $raws = $this->pg->query("SELECT * FROM test", \PDO::FETCH_ASSOC)->fetchAll();
        $this->assertEquals($raws[0]['created_by'], 'postgres');
        $this->assertEquals($raws[0]['updated_by'], 'postgres');
    }
}
