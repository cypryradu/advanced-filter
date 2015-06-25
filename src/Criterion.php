<?php
namespace CypryRadu\AdvancedFilter;

use CypryRadu\AdvancedFilter\ValueObject\DateVO;
use CypryRadu\AdvancedFilter\ValueObject\FieldVO;
use CypryRadu\AdvancedFilter\ValueObject\TableVO;

class Criterion
{
    private $type = 'where';
    private $fieldName;
    private $openParens = array();
    private $closedParens = array();
    private $link = 'AND';
    private $operator = '=';
    private $value = '';

    public function __construct($data = array())
    {
        if (!empty($data['field']))
            $this->fieldName = $data['field'];

        if (!empty($data['open_parens']))
            $this->openParens = $data['open_parens'];

        if (!empty($data['closed_parens']))
            $this->closedParens = $data['closed_parens'];

        if (!empty($data['link']))
            $this->link = $data['link'];

        if (!empty($data['operator']))
            $this->operator = $data['operator'];

        if (!empty($data['value']))
            $this->value = $data['value'];
    }

    public function setType($type)
    {
        $this->type = $type;

        return $this;
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
    private function buildFrom($builder, TableVO $fromTable, TableCollection $tablesUsed)
    {
        // set the FROM clause
        $doesBuildFrom = false;
        $fromTableName = $fromTable->getName();
        $fromTableAlias = $fromTable->getAlias();

        if (!$tablesUsed->count()) {
            $builder->from($fromTableName, $fromTableAlias);
            $tablesUsed->add($fromTable);
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
    private function buildJoins($builder, FieldVO $field, TableCollection $tables, TableCollection $tablesUsed)
    {
        $tablesUsedCount = $tablesUsed->count();
        $fromTable = $tables->first();

        $prevTableAlias = '';
        foreach ($field->getUseTables() as $tableKey) {
            if (!$tablesUsed->get($tableKey)) {

                if (empty($prevTableAlias)) {
                    $prevTableAlias = $fromTable->getAlias();
                }

                $table = $tables->get($tableKey);

                $tableName = $table->getName();
                $tableAlias = $table->getAlias();
                $joinType = $table->getJoinType();
                $joinOn = $table->getJoinOn();

                $builder->$joinType($prevTableAlias, $tableName, $tableAlias, $joinOn);

                $tablesUsed->add($table);
                $tablesUsedCount++;

                $prevTableAlias = $tableAlias;
            }
        }

        return $tablesUsedCount;
    }

    private function buildWhere($builder, $field)
    {
        $openParens = str_repeat('(', $this->calculateNumberOfParens($this->openParens));
        $closedParens = str_repeat(')', $this->calculateNumberOfParens($this->closedParens));

        $fieldName = $field->getFieldName();
        $fieldType = $field->getFieldType();
        $format = 'Y-m-d';
        $isoFormat = 'Y-m-d';

        if (!empty($this->value)) {
            $whereMethod = empty($this->link) ? 'where' : strtolower($this->link) . 'Where';
            $operator = $this->operator;

            $fieldValue = $this->value;

            switch ($fieldType) {
                case 'date':
                        if ($operator == '<=') {
                            $date = new DateVO($this->value, $format);
                            $date = $date->addInterval('P1D');
                            $fieldValue = $date->format($isoFormat);

                            $operator = '<';
                        }
                    break;
            }

            call_user_func(
                array($builder, $whereMethod),
                $openParens . $fieldName . ' ' . $operator . ' ' . $builder->createPositionalParameter($fieldValue) . $closedParens
            );
        }
    }

    public function build($builder, TableCollection $tables, TableCollection $tablesUsed, FieldCollection $fields)
    {
        $fromTable = $tables->first();
        $field = $fields->get($this->fieldName);

        if (!$field) {
            throw new \InvalidArgumentException('There is no field defined as "' . $this->fieldName . '"');
        }

        $doesBuildFrom = $this->buildFrom($builder, $fromTable, $tablesUsed);
        if ($doesBuildFrom) {
            $this->buildSelect($builder);
        }
        $this->buildJoins($builder, $field, $tables, $tablesUsed);
        $this->buildWhere($builder, $field);
    }
}
