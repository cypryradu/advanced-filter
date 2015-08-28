<?php

use CypryRadu\AdvancedFilter\Config;
use CypryRadu\AdvancedFilter\Criterion;
use CypryRadu\AdvancedFilter\Filter;
use CypryRadu\AdvancedFilter\FilterFactory;
use CypryRadu\AdvancedFilter\QueryBuilder\DbalQueryBuilder;
use Doctrine\DBAL\Configuration as DBALConfiguration;
use Doctrine\DBAL\DriverManager;

class FilterTest extends PHPUnit_Framework_TestCase
{
    private $db;

    private $tables;
    private $fields;
    private $columns;

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

        $config = new Config();
        $this->tables = $config->tables();
        $this->fields = $config->fields();
        $this->columns = $config->columns();

    }

    private function createQueryBuilder()
    {
        return new DbalQueryBuilder($this->db);
    }

    private function filterFactory()
    {
        return new FilterFactory($this->db, 'Dbal');
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testExceptionWhenFieldNotDefined()
    {
        $advancedFilter = $this->filterFactory()->create()
            ->tables($this->tables)
            ->fields($this->fields)
            ->addWhere(array(
                'field' => 'client_firstname', // this is not defined
                'operator' => '=',
                'value' => 'Ciprian',
                'link' => ''
            ));

        $builder = $advancedFilter->build();
    }


    /**
     * @expectedException InvalidArgumentException
     */
    public function testExceptionWhenTableNotDefined()
    {

        $advancedFilter = $this->filterFactory()->create()
            ->tables($this->tables)
            ->fields($this->fields)
            ->addWhere(array(
                'field' => 'invalid_table_field', // this is not defined
                'operator' => '=',
                'value' => 'Ciprian',
                'link' => '',
            ));

        $builder = $advancedFilter->build();
    }

    public function testWhenThereIsNoJoinAndASingleFieldWithNoDbFieldDefined()
    {
        $testQueryBuilder = $this->createQueryBuilder();
        $testQueryBuilder->select('*')
            ->from('clients', 'c') 
            ->where('c.`firstname` = ' . $testQueryBuilder->createPositionalParameter('Ciprian'))
        ;

        $advancedFilter = $this->filterFactory()->create()
            ->tables($this->tables)
            ->fields($this->fields)
            ->addWhere(array(
                'open_parens' => array(0, 0),
                'closed_parens' => array(0, 0),
                'field' => 'firstname',
                'operator' => '=',
                'value' => 'Ciprian',
            ));

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

        $advancedFilter = $this->filterFactory()->create()
            ->tables($this->tables)
            ->fields($this->fields)
            ->addWhere(array(
                'open_parens' => array(0, 0),
                'closed_parens' => array(0, 0),
                'field' => 'client_surname',
                'operator' => '=',
                'value' => 'Radu',
            ));
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

        $advancedFilter = $this->filterFactory()->create()
            ->tables($this->tables)
            ->fields($this->fields)
            ->addWhere(array(
                'open_parens' => array(0, 1),
                'closed_parens' => array(1, 0),
                'field' => 'firstname',
                'operator' => '=',
                'value' => 'Ciprian',
            ));

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

        $advancedFilter = $this->filterFactory()->create()
            ->tables($this->tables)
            ->fields($this->fields)
            ->addWhere(array(
                'field' => 'firstname',
                'operator' => '=',
                'value' => 'Ciprian',
            ))
            ->addWhere(array(
                'field' => 'client_surname',
                'operator' => '=',
                'value' => 'Radu',
                'link' => 'AND',
            ));

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

        $advancedFilter = $this->filterFactory()->create()
            ->tables($this->tables)
            ->fields($this->fields)
            ->addWhere(array(
                'open_parens' => array(0, 1),
                'closed_parens' => array(0, 0),
                'field' => 'firstname',
                'operator' => '=',
                'value' => 'Ciprian',
            ))
            ->addWhere(array(
                'open_parens' => array(0,0),
                'closed_parens' => array(1,0),
                'field' => 'client_surname',
                'operator' => '=',
                'value' => 'Radu',
                'link' => 'OR',
            ));

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

        $advancedFilter = $this->filterFactory()->create()
            ->tables($this->tables)
            ->fields($this->fields)
            ->addWhere(array(
                'field' => 'office',
                'operator' => '=',
                'value' => 'France',
            ));

        $queryBuilder = $advancedFilter->build();
        $this->assertEquals($testQueryBuilder, $queryBuilder);
    }


    /**
     * testWithDateField - If the operator is "<=" and we deal with a date, 
     *      we need to add one more day and change the operator to "<"
     */
    public function testWithDateField()
    {
        $testQueryBuilder = $this->createQueryBuilder();
        $testQueryBuilder->select('*')
            ->from('clients', 'c') 
            ->leftJoin('c', 'log', 'l', 'l.id = c.log_id') 
            ->where('l.`date_created` >= ' . $testQueryBuilder->createPositionalParameter('2013-02-01'))
            ->andWhere('l.`date_created` < ' . $testQueryBuilder->createPositionalParameter('2013-02-21'))
        ;

        $advancedFilter = $this->filterFactory()->create()
            ->tables($this->tables)
            ->fields($this->fields)
            ->addWhere(array(
                'field' => 'application_date',
                'operator' => '>=',
                'value' => '2013-02-01',
            ))
            ->addWhere(array(
                'field' => 'application_date',
                'operator' => '<=',
                'value' => '2013-02-20',
                'link' => 'AND',
            ));

        $queryBuilder = $advancedFilter->build();
        $this->assertEquals($testQueryBuilder, $queryBuilder);
    }

    public function testColumnsWithNoAlias()
    {
        $testQueryBuilder = $this->createQueryBuilder();
        $testQueryBuilder->select('*')
            ->from('clients', 'c') 
            ->select(array('c.firstname', 'c.surname', 'o.office'))
            ->leftJoin('c', 'offices', 'o', 'o.id = c.office_id') 
            ->where('c.`firstname` = ' . $testQueryBuilder->createPositionalParameter('Ciprian'))
        ;

        $advancedFilter = $this->filterFactory()->create()
            ->tables($this->tables)
            ->fields($this->fields)
            ->columns(array(
                'firstname',
                'client_surname',
                'office',
            ))
            ->addWhere(array(
                'field' => 'firstname',
                'operator' => '=',
                'value' => 'Ciprian',
            ));

        $queryBuilder = $advancedFilter->build();
        $this->assertEquals($testQueryBuilder, $queryBuilder);
    }

    public function testColumnsWithAlias()
    {
        $testQueryBuilder = $this->createQueryBuilder();
        $testQueryBuilder->select('*')
            ->from('clients', 'c') 
            ->select(array("c.firstname AS 'First Name'", 'c.surname', 'o.office'))
            ->leftJoin('c', 'offices', 'o', 'o.id = c.office_id') 
            ->where('c.`firstname` = ' . $testQueryBuilder->createPositionalParameter('Ciprian'))
        ;

        $advancedFilter = $this->filterFactory()->create()
            ->tables($this->tables)
            ->fields($this->fields)
            ->columns(array(
                'firstname' => 'First Name', // here is the column Alias
                'client_surname',
                'office',
            ))
            ->addWhere(array(
                'field' => 'firstname',
                'operator' => '=',
                'value' => 'Ciprian',
            ));

        $queryBuilder = $advancedFilter->build();
        $this->assertEquals($testQueryBuilder, $queryBuilder);
    }

    public function testWithColumnExpr()
    {
        $testQueryBuilder = $this->createQueryBuilder();
        $testQueryBuilder->select('*')
            ->from('clients', 'c') 
            ->select(array("c.firstname AS 'First Name'", 'c.surname', "GROUP_CONCAT(e.email) AS 'Emails'"))
            ->leftJoin('c', 'emails', 'e', 'e.client_id = c.id') 
            ->where('c.`firstname` = ' . $testQueryBuilder->createPositionalParameter('Ciprian'))
        ;

        $advancedFilter = $this->filterFactory()->create()
            ->tables($this->tables)
            ->fields($this->fields)
            ->columns(array(
                'firstname' => 'First Name',
                'client_surname',
                'emails' => 'Emails', // the emails field as a column expression behind
            ))
            ->addWhere(array(
                'field' => 'firstname',
                'operator' => '=',
                'value' => 'Ciprian',
            ));

        $queryBuilder = $advancedFilter->build();

        $this->assertEquals($testQueryBuilder, $queryBuilder);
    }
}
