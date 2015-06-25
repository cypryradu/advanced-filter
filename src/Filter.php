<?php
namespace CypryRadu\AdvancedFilter;

use Doctrine\DBAL\Query\QueryBuilder;
use CypryRadu\AdvancedFilter\ValueObject\TableVO;
use CypryRadu\AdvancedFilter\ValueObject\FieldVO;
use CypryRadu\AdvancedFilter\FieldCollection;
use CypryRadu\AdvancedFilter\TableCollection;

class Filter
{
    private $config;
    private $criteria;
    private $builder;
    private $usedTables = array();

    public function __construct(QueryBuilder $builder, Config $config)
    {
        $this->builder = $builder;
        $this->config = $config;

        $this->criteria = new Criteria();
    }

    public function addWhere(Criterion $criterion)
    {
        $this->criteria->add(
            $criterion->setType('where')
        );
    }

    public function addGroupBy(Criterion $criterion)
    {
        $this->criteria->add(
            $criterion->setType('group by')
        );
    }

    public function addHaving(Criterion $criterion)
    {
        $this->criteria->add(
            $criterion->setType('having')
        );
    }


    private function getTableCollection()
    {
        $tables = $this->config->tables();
        $collection = new TableCollection();

        foreach ($tables as $tableKey => $tableMeta) {
            $collection->add(new TableVO($tableKey, $tableMeta));            
        }
        
        return $collection;
    }

    private function getFieldCollection()
    {
        $fields = $this->config->fields();
        $collection = new FieldCollection();

        foreach ($fields as $fieldKey => $fieldMeta) {
            $collection->add(new FieldVO($fieldKey, $fieldMeta));            
        }
        
        return $collection;
    }

    public function build()
    {
	$tables = $this->getTableCollection();
        $usedTables = new TableCollection();
        $fields = $this->getFieldCollection();

	$this->criteria->build(
	    $this->builder, 
	    $tables, 
	    $usedTables,
            $fields
	);

        return $this->builder;
    }

    public function __toString()
    {
        return $this->getSql();
    }

}
