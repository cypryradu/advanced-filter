<?php
namespace CypryRadu\AdvancedFilter;

use Doctrine\DBAL\Query\QueryBuilder;
use CypryRadu\AdvancedFilter\ValueObject\Table;
use CypryRadu\AdvancedFilter\ValueObject\Field;

class Filter
{
    private $config;
    private $criteria;
    private $builder;
    private $usedTables = array();
    
    public function __construct(QueryBuilder $builder, Config $config, Criteria $criteria)
    {
        $this->builder = $builder;
        $this->config = $config;
        $this->criteria = $criteria;
    }
    
    public function addUsedTable($table)
    {
        $this->usedTables[$table->getKey()] = $table;
    }
    
    public function isTableUsed($tableKey)
    {
        return isset($this->usedTables[$tableKey]);
    }
    
    public function getUsedTables()
    {
        return $this->usedTables;
    }
    
    public function getFieldMetaByName($fieldName)
    {
        $fields = $this->config->fields();
        if (!isset($fields[$fieldName])) {
            throw new \InvalidArgumentException('There is no field defined as "' . $fieldName . '"');
        }
        return $fields[$fieldName];
    }
    
    public function getField($fieldKey)
    {
        $fieldMeta = $this->getFieldMetaByName($fieldKey);
        return new Field($fieldKey, $fieldMeta);
    }
    
    public function getTableMetaByName($tableKey)
    {
        $tables = $this->config->tables();
        if (!isset($tables[$tableKey])) {
            throw new \InvalidArgumentException('There is no table defined as "' . $tableKey . '"');
        }
        return $tables[$tableKey];
    }
    
    public function getTable($tableKey)
    {
        $tableMeta = $this->getTableMetaByName($tableKey);
        return new Table($tableKey, $tableMeta);
    }
    
    
    public function getFromTable()
    {
        $tables = $this->config->tables();
        reset($tables);
        $tableKey = key($tables);
        $tableMeta = $this->getTableMetaByName($tableKey);
        return new Table($tableKey, $tableMeta);
    }
    
    public function build()
    {
        foreach ($this->criteria->getAll() as $criterion) {
            $criterion->build($this, $this->builder, $this->config);
        }
        
        return $this->builder;
    }
    
    public function __toString()
    {
        return $this->getSql();
    }
    
}