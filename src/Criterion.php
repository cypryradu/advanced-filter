<?php
namespace CypryRadu\AdvancedFilter;

use CypryRadu\AdvancedFilter\ValueObject\DateVO;

class Criterion
{
    private $type = 'where';
    private $field;
    private $openParens = array();
    private $closedParens = array();
    private $link = 'AND';
    private $operator = '=';
    private $value = '';

    public function __construct($data = array())
    {
        if (!empty($data['field']))
            $this->field = $data['field'];

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

        $prevTableAlias = '';
        foreach ($field->getUseTables() as $tableKey) {
            if (!$filter->isTableUsed($tableKey)) {

                if (empty($prevTableAlias)) {
                    $prevTableAlias = $filter->getFromTable()->getAlias();
                }

                $table = $filter->getTable($tableKey);

                $tableName = $table->getName();
                $tableAlias = $table->getAlias();
                $joinType = $table->getJoinType();
                $joinOn = $table->getJoinOn();

                $builder->$joinType($prevTableAlias, $tableName, $tableAlias, $joinOn);

                $filter->addUsedTable($table);
                $tablesUsed++;

                $prevTableAlias = $tableAlias;
            }
        }

        return $tablesUsed;
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
            $builder->$whereMethod($openParens . $fieldName . ' ' . $operator . ' ' . $builder->createPositionalParameter($fieldValue) . $closedParens);
        }
    }

    public function build($filter, $builder, $config)
    {
        $field = $filter->getField($this->field);

        $doesBuildFrom = $this->buildFrom($filter, $builder);
        if ($doesBuildFrom) {
            $this->buildSelect($builder);
        }
        $this->buildJoins($filter, $builder, $field);
        $this->buildWhere($builder, $field);

    }
}
