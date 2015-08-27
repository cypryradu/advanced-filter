<?php

namespace CypryRadu\AdvancedFilter;

use CypryRadu\AdvancedFilter\QueryBuilder\QueryBuilderInterface;
use CypryRadu\AdvancedFilter\ValueObject\DateVO;
use CypryRadu\AdvancedFilter\ValueObject\FieldVO;
use CypryRadu\AdvancedFilter\ValueObject\TableVO;

/**
 * Represents an individual filter unit.
 *
 * @author Ciprian Radu <cypryradu@gmail.com>
 */
class Criterion
{
    /**
     * @var string = 'where'
     */
    private $type = 'where';
    /**
     * @var string The fieldKey defined in Config
     */
    private $fieldName;
    /**
     * @var array The state of the surrounding open parens.
     *
     * @example array(0, 1, ...) - only the second is open
     */
    private $openParens = array();
    /**
     * @var array The state of the surrounding closed parens.
     *
     * @example array(1, 0, ...) - only the first is closed
     */
    private $closedParens = array();
    /**
     * @var string = 'AND'
     */
    private $link = 'AND';
    /**
     * @var string = '='
     */
    private $operator = '=';
    /**
     * @var string = ''
     */
    private $value = '';

    /**
     * Constructor.
     *
     * @param array $data
     *
     * @example - see unit tests
     */
    public function __construct($data = array())
    {
        if (!empty($data['field'])) {
            $this->fieldName = $data['field'];
        }

        if (!empty($data['open_parens'])) {
            $this->openParens = $data['open_parens'];
        }

        if (!empty($data['closed_parens'])) {
            $this->closedParens = $data['closed_parens'];
        }

        if (!empty($data['link'])) {
            $this->link = $data['link'];
        }

        if (!empty($data['operator'])) {
            $this->operator = $data['operator'];
        }

        if (!empty($data['value'])) {
            $this->value = $data['value'];
        }
    }

    /**
     * @param string $type
     *
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Calculate the number of activated parens.
     *
     * @param array $parens
     *
     * @return int Number of activated parens
     */
    private function calculateNumberOfParens(&$parens)
    {
        $count = 0;
        if (isset($parens) && is_array($parens)) {
            $count = count(array_filter($parens, function ($val) {
                return $val == 1;
            }));
        }

        return $count;
    }

    /**
     * Adds the FROM clause.
     *
     * @param \CypryRadu\AdvancedFilter\QueryBuilder\QueryBuilderInterface $builder
     * @param \CypryRadu\AdvancedFilter\ValueObject\TableVO                $fromTable
     * @param \CypryRadu\AdvancedFilter\ValueObject\TableVO                $fromTable
     *
     * @return bool TRUE only the first time when the FROM clause is added, FALSE otherwise
     */
    private function buildFrom(QueryBuilderInterface $builder, TableVO $fromTable, TableCollection $tablesUsed)
    {
        $doesBuildFrom = false;
        $fromTableName = $fromTable->getName();
        $fromTableAlias = $fromTable->getAlias();

        // Adds the FROM clause only the first time, when there's no table used yet
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
     * Adds the necessary joins for this field
     *
     * @param \CypryRadu\AdvancedFilter\QueryBuilder\QueryBuilderInterface $builder
     * @param \CypryRadu\AdvancedFilter\ValueObject\FieldVO $field
     * @param \CypryRadu\AdvancedFilter\ValueObject\TableCollection $tables
     * @param \CypryRadu\AdvancedFilter\ValueObject\TableCollection $tablesUsed
     *
     * @return integer How many tables were used so far
    */
    private function buildJoins(QueryBuilderInterface $builder, FieldVO $field, TableCollection $tables, TableCollection $tablesUsed)
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
                ++$tablesUsedCount;

                $prevTableAlias = $tableAlias;
            }
        }

        return $tablesUsedCount;
    }

    /**
     * Builds the WHERE clause.
     *
     * @param \CypryRadu\AdvancedFilter\QueryBuilder\QueryBuilderInterface $builder
     * @param \CypryRadu\AdvancedFilter\ValueObject\FieldVO                $field
     */
    private function buildWhere(QueryBuilderInterface $builder, FieldVO $field)
    {
        $openParens = str_repeat('(', $this->calculateNumberOfParens($this->openParens));
        $closedParens = str_repeat(')', $this->calculateNumberOfParens($this->closedParens));

        $fieldName = $field->getFieldName();
        $fieldType = $field->getFieldType();
        $format = 'Y-m-d';
        $isoFormat = 'Y-m-d';

        if (!empty($this->value)) {
            $whereMethod = empty($this->link) ? 'where' : strtolower($this->link).'Where';
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
                $openParens.$fieldName.' '.$operator.' '.$builder->createPositionalParameter($fieldValue).$closedParens
            );
        }
    }

    /**
     * Builds the QueryBuilder for individual Criterion(s)
     * It propagates from Criteria collection object.
     *
     * @param \CypryRadu\AdvancedFilter\QueryBuilder\QueryBuilderInterface $builder
     * @param \CypryRadu\AdvancedFilter\TableCollection                    $tables
     * @param \CypryRadu\AdvancedFilter\TableCollection                    $tablesUsed
     * @param \CypryRadu\AdvancedFilter\FieldCollection                    $fields
     *
     * @throws InvalidArgumentException If the given field name cannot be find
     *                                  in the Config->fields() array
     */
    public function build(QueryBuilderInterface $builder, TableCollection $tables, TableCollection $tablesUsed, FieldCollection $fields)
    {
        $fromTable = $tables->first();
        $field = $fields->get($this->fieldName);

        if (!$field) {
            throw new \InvalidArgumentException('There is no field defined as "'.$this->fieldName.'"');
        }

        $doesBuildFrom = $this->buildFrom($builder, $fromTable, $tablesUsed);
        if ($doesBuildFrom) {
            $this->buildSelect($builder);
        }
        $this->buildJoins($builder, $field, $tables, $tablesUsed);
        $this->buildWhere($builder, $field);
    }
}
