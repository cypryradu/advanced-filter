<?php
namespace CypryRadu\AdvancedFilter\ValueObject;

class Field 
{
    private $fieldKey;
    private $metaData;
    
    public function __construct($fieldKey, $metaData)
    {
        $this->fieldKey = $fieldKey;
        $this->metaData = $metaData;
    }
    
    public function getKey()
    {
        return $this->fieldKey;
    }
    
    public function getDbField()
    {
        return (!empty($this->metaData['db_field']) 
            ? $this->metaData['db_field'] 
            : $this->fieldKey
        );
    }
    
    public function getUseTables()
    {
        if (!isset($this->metaData['use_tables']) || !is_array($this->metaData['use_tables'])) {
            return array();
        }
        
        return $this->metaData['use_tables'];
    }
    
    public function getTableAlias()
    {
        return (!empty($this->metaData['table_alias']) ? $this->metaData['table_alias'] . '.' : '');
    }
    
    public function getFieldName()
    {
        return $this->getTableAlias() . '`' . $this->getDbField() . '`';
    }
    
    
}