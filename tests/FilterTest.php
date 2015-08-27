<?php

use CypryRadu\AdvancedFilter\Config;
use CypryRadu\AdvancedFilter\Criterion;
use CypryRadu\AdvancedFilter\Filter;
use CypryRadu\AdvancedFilter\QueryBuilder\DbalQueryBuilder;
use Doctrine\DBAL\Configuration as DBALConfiguration;
use Doctrine\DBAL\DriverManager;

class FilterTest extends PHPUnit_Framework_TestCase
{
    private $db;

    public function setUp()
    {
        $config = new DBALConfiguration();
        $connectionParams = array(
            'dbname' => 'test',
            'user' => '',
            'password' => '',
            'host' => 'localhost',
            'charset' => 'utf8',
            'driver' => 'pdo_mysql',
        );
        $this->db = DriverManager::getConnection($connectionParams, $config);
    }

    private function createQueryBuilder()
    {
        return new DbalQueryBuilder($this->db);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testExceptionWhenFieldNotDefined()
    {
        $queryBuilder = $this->createQueryBuilder();

        $filterConfig = new Config();

        $advancedFilter = new Filter($queryBuilder, $filterConfig);
        $advancedFilter->addWhere(new Criterion(array(
            'field' => 'client_firstname', // this is not defined
            'operator' => '=',
            'value' => 'Ciprian',
            'link' => ''
        )));
        $builder = $advancedFilter->build();
    }


    /**
     * @expectedException InvalidArgumentException
     */
    public function testExceptionWhenTableNotDefined()
    {
        $queryBuilder = $this->createQueryBuilder();

        $filterConfig = new Config();

        $advancedFilter = new Filter($queryBuilder, $filterConfig);
        $advancedFilter->addWhere(new Criterion(array(
            'field' => 'invalid_table_field', // this is not defined
            'operator' => '=',
            'value' => 'Ciprian',
            'link' => '',
        )));
        $builder = $advancedFilter->build();
    }

    public function testWhenThereIsNoJoinAndASingleFieldWithNoDbFieldDefined()
    {
        $testQueryBuilder = $this->createQueryBuilder();
        $testQueryBuilder->select('*')
            ->from('clients', 'c') 
            ->where('c.`firstname` = ' . $testQueryBuilder->createPositionalParameter('Ciprian'))
        ;
        $queryBuilder = $this->createQueryBuilder();

        $filterConfig = new Config();

        $advancedFilter = new Filter($queryBuilder, $filterConfig);
        $advancedFilter->addWhere(new Criterion(array(
            'open_parens' => array(0, 0),
            'closed_parens' => array(0, 0),
            'field' => 'firstname',
            'operator' => '=',
            'value' => 'Ciprian',
        )));
        $queryBuilder = $advancedFilter->build();
        $this->assertEquals($testQueryBuilder, $queryBuilder);
    }

    public function testWhenThereIsNoJoinAndASingleFieldWithDbFieldDefined()
    {
        $testQueryBuilder = $this->createQueryBuilder();
        $testQueryBuilder->select('*')
            ->from('clients', 'c') 
            ->where('c.`surname` = ' . $testQueryBuilder->createPositionalParameter('Radu'))
        ;
        $queryBuilder = $this->createQueryBuilder();

        $filterConfig = new Config();

        $advancedFilter = new Filter($queryBuilder, $filterConfig);
        $advancedFilter->addWhere(new Criterion(array(
            'open_parens' => array(0, 0),
            'closed_parens' => array(0, 0),
            'field' => 'client_surname',
            'operator' => '=',
            'value' => 'Radu',
        )));
        $queryBuilder = $advancedFilter->build();
        $this->assertEquals($testQueryBuilder, $queryBuilder);
    }

    public function testWhenThereIsNoJoinAndASingleFieldWithParens()
    {
        $testQueryBuilder = $this->createQueryBuilder();
        $testQueryBuilder->select('*')
            ->from('clients', 'c') 
            ->where('(c.`firstname` = ' . $testQueryBuilder->createPositionalParameter('Ciprian') . ')')
        ;
        $queryBuilder = $this->createQueryBuilder();

        $filterConfig = new Config();

        $advancedFilter = new Filter($queryBuilder, $filterConfig);
        $advancedFilter->addWhere(new Criterion(array(
            'open_parens' => array(0, 1),
            'closed_parens' => array(1, 0),
            'field' => 'firstname',
            'operator' => '=',
            'value' => 'Ciprian',
        )));
        $queryBuilder = $advancedFilter->build();
        $this->assertEquals($testQueryBuilder, $queryBuilder);
    }

    public function testWhenThereIsNoJoinAndMoreFields()
    {
        $testQueryBuilder = $this->createQueryBuilder();
        $testQueryBuilder->select('*')
            ->from('clients', 'c') 
            ->where('c.`firstname` = ' . $testQueryBuilder->createPositionalParameter('Ciprian'))
            ->andWhere('c.`surname` = ' . $testQueryBuilder->createPositionalParameter('Radu'))
        ;
        $queryBuilder = $this->createQueryBuilder();

        $filterConfig = new Config();

        $advancedFilter = new Filter($queryBuilder, $filterConfig);
        $advancedFilter->addWhere(new Criterion(array(
            'field' => 'firstname',
            'operator' => '=',
            'value' => 'Ciprian',
        )));
        $advancedFilter->addWhere(new Criterion(array(
            'field' => 'client_surname',
            'operator' => '=',
            'value' => 'Radu',
            'link' => 'AND',
        )));
        $queryBuilder = $advancedFilter->build();
        $this->assertEquals($testQueryBuilder, $queryBuilder);
    }

    public function testWhenThereIsNoJoinAndMoreFieldsWithParens()
    {
        $testQueryBuilder = $this->createQueryBuilder();
        $testQueryBuilder->select('*')
            ->from('clients', 'c') 
            ->where('(c.`firstname` = ' . $testQueryBuilder->createPositionalParameter('Ciprian'))
            ->orWhere('c.`surname` = ' . $testQueryBuilder->createPositionalParameter('Radu') . ')')
        ;
        $queryBuilder = $this->createQueryBuilder();

        $filterConfig = new Config();

        $advancedFilter = new Filter($queryBuilder, $filterConfig);
        $advancedFilter->addWhere(new Criterion(array(
            'open_parens' => array(0, 1),
            'closed_parens' => array(0, 0),
            'field' => 'firstname',
            'operator' => '=',
            'value' => 'Ciprian',
        )));
        $advancedFilter->addWhere(new Criterion(array(
            'open_parens' => array(0,0),
            'closed_parens' => array(1,0),
            'field' => 'client_surname',
            'operator' => '=',
            'value' => 'Radu',
            'link' => 'OR',
        )));
        $queryBuilder = $advancedFilter->build();
        $this->assertEquals($testQueryBuilder, $queryBuilder);
    }

    public function testOneJoinAndASingleField()
    {
        $testQueryBuilder = $this->createQueryBuilder();
        $testQueryBuilder->select('*')
            ->from('clients', 'c') 
            ->leftJoin('c', 'offices', 'o', 'o.id = c.office_id') 
            ->where('o.`office` = ' . $testQueryBuilder->createPositionalParameter('France'))
        ;
        $queryBuilder = $this->createQueryBuilder();

        $filterConfig = new Config($this->db);

        $advancedFilter = new Filter($queryBuilder, $filterConfig);
        $advancedFilter->addWhere(new Criterion(array(
            'field' => 'office',
            'operator' => '=',
            'value' => 'France',
        )));
        $queryBuilder = $advancedFilter->build();
        $this->assertEquals($testQueryBuilder, $queryBuilder);
    }

    public function testWithDateField()
    {
        $filterConfig = new Config($this->db);

        $testQueryBuilder = $this->createQueryBuilder();
        $testQueryBuilder->select('*')
            ->from('clients', 'c') 
            ->leftJoin('c', 'log', 'l', 'l.id = c.log_id') 
            ->where('l.`date_created` >= ' . $testQueryBuilder->createPositionalParameter('2013-02-01'))
            ->andWhere('l.`date_created` < ' . $testQueryBuilder->createPositionalParameter('2013-02-21'))
        ;
        $queryBuilder = $this->createQueryBuilder();

        $filterConfig = new Config($this->db);

        $advancedFilter = new Filter($queryBuilder, $filterConfig);
        $advancedFilter->addWhere(new Criterion(array(
            'field' => 'application_date',
            'operator' => '>=',
            'value' => '2013-02-01',
        )));
        $advancedFilter->addWhere(new Criterion(array(
            'field' => 'application_date',
            'operator' => '<=',
            'value' => '2013-02-20',
            'link' => 'AND',
        )));
        $queryBuilder = $advancedFilter->build();
        $this->assertEquals($testQueryBuilder, $queryBuilder);
    }
}
