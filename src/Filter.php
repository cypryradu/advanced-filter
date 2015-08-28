<?php

namespace CypryRadu\AdvancedFilter;

use CypryRadu\AdvancedFilter\QueryBuilder\QueryBuilderInterface;
use CypryRadu\AdvancedFilter\ValueObject\TableVO;
use CypryRadu\AdvancedFilter\ValueObject\FieldVO;

/**
 * The entry point class for creating the filter.
 *
 * @author Ciprian Radu <cypryradu@gmail.com>
 */
class Filter
{
    /**
     * @var \CypryRadu\AdvancedFilter\Criteria
     */
    private $criteria;
    /**
     * @var \CypryRadu\AdvancedFilter\QueryBuilder\QueryBuilderInterface
     */
    private $builder;
    /**
     * @var array
     */
    private $usedTables = array();

    /**
     * @var array
     */
    private $tables = array();

    /**
     * @var array
     */
    private $fields = array();

    /**
     * @var columns
     */
    private $columns = array();

    /**
     * Constructor.
     *
     * @param \CypryRadu\AdvancedFilter\QueryBuilder\QueryBuilderInterface $builder
     */
    public function __construct(QueryBuilderInterface $builder)
    {
        $this->builder = $builder;

        $this->criteria = new Criteria();
    }

    /**
     * Set the tables joining definition
     * 
     * @param array $tables 
     * @return $this
     */
    public function tables(array $tables)
    {
        $this->tables = $tables;

        return $this;
    }

    /**
     * Set the fields definition
     * 
     * @param array $fields
     * @return $this
     */
    public function fields($fields)
    {
        $this->fields = $fields;

        return $this;
    }

    /**
     * Set the columns definition
     * 
     * @param array $columns
     * @return $this
     */
    public function columns($columns)
    {
        $this->columns = $columns;

        return $this;
    }

    /**
     * Adds an WHERE criterion.
     *
     * @param array $criterion
     * @return $this
     */
    public function addWhere(array $criterion)
    {
        $criterion = new Criterion($criterion);
        $this->criteria->add(
            $criterion->setType('where')
        );

        return $this;
    }

    /**
     * Adds a GROUP BY criterion.
     *
     * @param array $criterion
     * @return $this
     */
    public function addGroupBy(array $criterion)
    {
        $criterion = new Criterion($criterion);
        $this->criteria->add(
            $criterion->setType('group by')
        );

        return $this;
    }

    /**
     * Adds a HAVING criterion.
     *
     * @param array $criterion
     * @return $this
     */
    public function addHaving(array $criterion)
    {
        $criterion = new Criterion($criterion);
        $this->criteria->add(
            $criterion->setType('having')
        );

        return $this;
    }

    /**
     * Gets a collection of TableVO objects.
     *
     * @return \CypryRadu\AdvancedFilter\TableCollection
     */
    private function getTableCollection()
    {
        $tables = $this->tables;
        $collection = new TableCollection();

        foreach ($tables as $tableKey => $tableMeta) {
            $collection->add(new TableVO($tableKey, $tableMeta));
        }

        return $collection;
    }

    /**
     * Gets a collection of FieldVO objects.
     *
     * @return \CypryRadu\AdvancedFilter\FieldCollection
     */
    private function getFieldCollection()
    {
        $fields = $this->fields;
        $collection = new FieldCollection();

        foreach ($fields as $fieldKey => $fieldMeta) {
            $collection->add(new FieldVO($fieldKey, $fieldMeta));
        }

        return $collection;
    }

    /**
     * Builds the QueryBuilder on the information provided, in fields, tables, columns, and other criteria
     *
     * @return \CypryRadu\AdvancedFilter\QueryBuilder\QueryBuilderInterface
     * @throws InvalidArgumentException If the fields were not provided
     * @throws InvalidArgumentException If the tables were not provided
     */
    public function build()
    {
        if (empty($this->tables)) {
            throw new \InvalidArgumentException('Please define your tables first using the tables() method');
        }

        if (empty($this->fields)) {
            throw new \InvalidArgumentException('Please define your fields first using the fields() method');
        }

        $tables = $this->getTableCollection();
        $usedTables = new TableCollection();

        $fields = $this->getFieldCollection();
        $columns = $this->columns ? $this->columns : array('*');

        $this->criteria->build(
            $this->builder,
            $tables,
            $usedTables,
            $fields,
            $columns
        );

        return $this->builder;
    }

    /**
     * Converts the internal QueryBuilder to SQL string.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->builder->getSql();
    }
}
