<?php
use CypryRadu\AdvancedFilter\Config;
use CypryRadu\AdvancedFilter\Criteria;
use CypryRadu\AdvancedFilter\Criterion;
use CypryRadu\AdvancedFilter\Filter;
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
    
    
    /**
     * @expectedException InvalidArgumentException
     */
    public function testExceptionWhenNotFieldNotDefined()
    {
        $queryBuilder = $this->db->createQueryBuilder();
        
        $filterConfig = new Config();
        $criteria = new Criteria();
        $criterion = new Criterion(array(
            'field' => 'volunteer_firstname', // this is not defined
            'operator' => '=',
            'value' => 'Ciprian',
            'link' => ''
        ));
        
        $criteria->add($criterion);
        
        $advancedFilter = new Filter($queryBuilder, $filterConfig, $criteria);
        $builder = $advancedFilter->build();
    }
    
    public function testWhenThereIsNoJoinAndASingleFieldWithNoDbFieldDefined()
    {
        $testQueryBuilder = $this->db->createQueryBuilder();
        $testQueryBuilder->select('*')
            ->from('marketing__volunteers', 'mv') 
            ->where('mv.`firstname` = ' . $testQueryBuilder->createPositionalParameter('Ciprian'))
        ;
        $queryBuilder = $this->db->createQueryBuilder();
        
        $filterConfig = new Config();
        $criteria = new Criteria();
        $criterion = new Criterion(array(
            'open_parens' => array(0, 0),
            'closed_parens' => array(0, 0),
            'field' => 'firstname',
            'operator' => '=',
            'value' => 'Ciprian',
            'link' => ''
        ));
        
        $criteria->add($criterion);
        
        $advancedFilter = new Filter($queryBuilder, $filterConfig, $criteria);
        $queryBuilder = $advancedFilter->build();
        $this->assertEquals($testQueryBuilder,$queryBuilder);
    }
    
    public function testWhenThereIsNoJoinAndASingleFieldWithDbFieldDefined()
    {
        $testQueryBuilder = $this->db->createQueryBuilder();
        $testQueryBuilder->select('*')
            ->from('marketing__volunteers', 'mv') 
            ->where('mv.`surname` = ' . $testQueryBuilder->createPositionalParameter('Radu'))
        ;
        $queryBuilder = $this->db->createQueryBuilder();
        
        $filterConfig = new Config();
        $criteria = new Criteria();
        $criterion = new Criterion(array(
            'open_parens' => array(0, 0),
            'closed_parens' => array(0, 0),
            'field' => 'volunteer_surname',
            'operator' => '=',
            'value' => 'Radu',
            'link' => ''
        ));
        
        $criteria->add($criterion);
        
        $advancedFilter = new Filter($queryBuilder, $filterConfig, $criteria);
        $queryBuilder = $advancedFilter->build();
        $this->assertEquals($testQueryBuilder,$queryBuilder);
    }
    
    public function testWhenThereIsNoJoinAndASingleFieldWithParens()
    {
        $testQueryBuilder = $this->db->createQueryBuilder();
        $testQueryBuilder->select('*')
            ->from('marketing__volunteers', 'mv') 
            ->where('(mv.`firstname` = ' . $testQueryBuilder->createPositionalParameter('Ciprian') . ')')
        ;
        $queryBuilder = $this->db->createQueryBuilder();
        
        $filterConfig = new Config();
        $criteria = new Criteria();
        $criterion = new Criterion(array(
            'open_parens' => array(0, 1),
            'closed_parens' => array(1, 0),
            'field' => 'firstname',
            'operator' => '=',
            'value' => 'Ciprian',
            'link' => ''
        ));
        
        $criteria->add($criterion);
        
        $advancedFilter = new Filter($queryBuilder, $filterConfig, $criteria);
        $queryBuilder = $advancedFilter->build();
        $this->assertEquals($testQueryBuilder,$queryBuilder);
    }
    
    public function testWhenThereIsNoJoinAndMoreFields()
    {
        $testQueryBuilder = $this->db->createQueryBuilder();
        $testQueryBuilder->select('*')
            ->from('marketing__volunteers', 'mv') 
            ->where('mv.`firstname` = ' . $testQueryBuilder->createPositionalParameter('Ciprian'))
            ->andWhere('mv.`surname` = ' . $testQueryBuilder->createPositionalParameter('Radu'))
        ;
        $queryBuilder = $this->db->createQueryBuilder();
        
        $filterConfig = new Config();
        $criteria = new Criteria();
        $criterion1 = new Criterion(array(
            'field' => 'firstname',
            'operator' => '=',
            'value' => 'Ciprian',
            'link' => ''
        ));
        
        $criterion2 = new Criterion(array(
            'field' => 'volunteer_surname',
            'operator' => '=',
            'value' => 'Radu',
            'link' => 'AND'
        ));
        
        $criteria->add($criterion1);
        $criteria->add($criterion2);
        
        $advancedFilter = new Filter($queryBuilder, $filterConfig, $criteria);
        $queryBuilder = $advancedFilter->build();
        $this->assertEquals($testQueryBuilder, $queryBuilder);
    }
    
    public function testWhenThereIsNoJoinAndMoreFieldsWithParens()
    {
        $testQueryBuilder = $this->db->createQueryBuilder();
        $testQueryBuilder->select('*')
            ->from('marketing__volunteers', 'mv') 
            ->where('(mv.`firstname` = ' . $testQueryBuilder->createPositionalParameter('Ciprian'))
            ->orWhere('mv.`surname` = ' . $testQueryBuilder->createPositionalParameter('Radu') . ')')
        ;
        $queryBuilder = $this->db->createQueryBuilder();
        
        $filterConfig = new Config();
        $criteria = new Criteria();
        $criterion1 = new Criterion(array(
            'open_parens' => array(0,1),
            'closed_parens' => array(0,0),
            'field' => 'firstname',
            'operator' => '=',
            'value' => 'Ciprian',
            'link' => ''
        ));
        
        $criterion2 = new Criterion(array(
            'open_parens' => array(0,0),
            'closed_parens' => array(1,0),
            'field' => 'volunteer_surname',
            'operator' => '=',
            'value' => 'Radu',
            'link' => 'OR'
        ));
        
        $criteria->add($criterion1);
        $criteria->add($criterion2);
        
        $advancedFilter = new Filter($queryBuilder, $filterConfig, $criteria);
        $queryBuilder = $advancedFilter->build();
        $this->assertEquals($testQueryBuilder, $queryBuilder);
    }
    
    public function testOneJoinAndASingleField()
    {
        $testQueryBuilder = $this->db->createQueryBuilder();
        $testQueryBuilder->select('*')
            ->from('marketing__volunteers', 'mv') 
            ->leftJoin('mv', 'recruitment_offices', 'ro', 'ro.id = mv.recruitment_office_id') 
            ->where('ro.`recruitment_office` = ' . $testQueryBuilder->createPositionalParameter('France'))
        ;
        $queryBuilder = $this->db->createQueryBuilder();
        
        $filterConfig = new Config($this->db);
        $criteria = new Criteria();
        $criterion = new Criterion(array(
            'field' => 'recruitment_office',
            'operator' => '=',
            'value' => 'France',
        ));
        
        $criteria->add($criterion);
        
        $advancedFilter = new Filter($queryBuilder, $filterConfig, $criteria);
        $queryBuilder = $advancedFilter->build();
        $this->assertEquals($testQueryBuilder, $queryBuilder);
    }
    
/*
    public function testFieldsCallback()
    {
        $filterConfig = new Config($this->db);
        
        $callbacks = $filterConfig->fieldsCallback();
        $data = $callbacks['recruitment_office']();
        
        print_r($data); die;
    }
*/
    
}