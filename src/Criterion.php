<?php
namespace CypryRadu\AdvancedFilter;

class Criterion
{
    private $data = array();
    
    public function __construct($data)
    {
        $this->data = $data;
    }
    
    private function calculateNumberOfParens(&$parens)
    {
        $count = 0;
        if (isset($parens) && is_array($parens)) {
            $count = count(array_filter($parens, function($val) {
                return $val == 1;
            }));
        }
        
        return $count;
    }
    
    /*
    * Add the FROM clause
    */
    private function buildFrom($filter, $builder)
    {
        $fromTable = $filter->getFromTable();
        
        // set the FROM clause
        $doesBuildFrom = false;
        $fromTableName = $fromTable->getName();
        $fromTableAlias = $fromTable->getAlias();
        
        if (!$filter->getUsedTables()) {
            $builder->from($fromTableName, $fromTableAlias);
            $filter->addUsedTable($fromTable);
            $doesBuildFrom = true;
        }
        
        return $doesBuildFrom;
    }
    
    private function buildSelect($builder)
    {
        $builder->select('*');
    }
    
    /*
    * Add the necessary joins for this field
    */
    private function buildJoins($filter, $builder, $field)
    {
        $tablesUsed = count($filter->getUsedTables());
        
        foreach ($field->getUseTables() as $tableKey) {
            if (!$filter->isTableUsed($tableKey)) {
                
                $table = $filter->getTable($tableKey);
                $fromTableAlias = $filter->getFromTable()->getAlias();
                
                $tableName = $table->getName();
                $tableAlias = $table->getAlias();
                $joinType = $table->getJoinType();
                $joinOn = $table->getJoinOn();
                
                $builder->$joinType($fromTableAlias, $tableName, $tableAlias, $joinOn);
                
                $filter->addUsedTable($table);
                $tablesUsed++;
            }
        }
        
        return $tablesUsed;
        
    }
    
    private function buildWhere($builder, $field)
    {
        $openParens = str_repeat('(', $this->calculateNumberOfParens($this->data['open_parens']));
        $closedParens = str_repeat(')', $this->calculateNumberOfParens($this->data['closed_parens']));
        
        $fieldName = $field->getFieldName();

        if (!empty($this->data['value'])) {
            $whereMethod = empty($this->data['link']) ? 'where' : strtolower($this->data['link']) . 'Where';
            $operator = $this->data['operator'];
            $builder->$whereMethod($openParens . $fieldName . ' ' . $operator . ' ' . $builder->createPositionalParameter($this->data['value']) . $closedParens);
        }
    }
    
    public function build($filter, $builder, $config)
    {
        $field = $filter->getField($this->data['field']);
        
        $doesBuildFrom = $this->buildFrom($filter, $builder);
        if ($doesBuildFrom) {
            $this->buildSelect($builder);
        }
        $this->buildJoins($filter, $builder, $field);
        $this->buildWhere($builder, $field);
        
    }
}