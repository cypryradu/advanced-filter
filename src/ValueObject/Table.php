<?php
namespace CypryRadu\AdvancedFilter\ValueObject;

class Table 
{
    private $tableKey;
    private $tableName;
    private $tableAlias;
    private $metaData;
    private $joinType;
    private $joinOn;
    
    public function __construct($tableKey, $metaData)
    {
        list($tableName, $tableAlias) = array_reverse(explode('.', $tableKey));
        $this->tableKey = $tableKey;
        $this->tableName = $tableName;
        $this->tableAlias = $tableAlias;
        $this->metaData = $metaData;
        $this->joinType = array_shift($metaData);
        $this->joinOn = array_shift($metaData);
    }
    
    public function getKey()
    {
        return $this->tableKey;
    }
    
    public function getName()
    {
        return $this->tableName;
    }
    
    public function getAlias()
    {
        return $this->tableAlias;
    }
    
    public function getJoinType()
    {
        return $this->joinType;
    }
    
    public function getJoinOn()
    {
        return $this->joinOn;
    }
    
}