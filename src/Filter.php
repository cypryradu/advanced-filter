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
     * @var \CypryRadu\AdvancedFilter\FilterConfigInterface
     */
    private $config;
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
     * Constructor.
     *
     * @param \CypryRadu\AdvancedFilter\QueryBuilder\QueryBuilderInterface $builder
     * @param \CypryRadu\AdvancedFilter\FilterConfigInterface $config
     */
    public function __construct(QueryBuilderInterface $builder, FilterConfigInterface $config)
    {
        $this->builder = $builder;
        $this->config = $config;

        $this->criteria = new Criteria();
    }

    /**
     * Adds an WHERE criterion.
     *
     * @param \CypryRadu\AdvancedFilter\Criterion $criterion
     */
    public function addWhere(Criterion $criterion)
    {
        $this->criteria->add(
            $criterion->setType('where')
        );
    }

    /**
     * Adds a GROUP BY criterion.
     *
     * @param \CypryRadu\AdvancedFilter\Criterion $criterion
     */
    public function addGroupBy(Criterion $criterion)
    {
        $this->criteria->add(
            $criterion->setType('group by')
        );
    }

    /**
     * Adds a HAVING criterion.
     *
     * @param \CypryRadu\AdvancedFilter\Criterion $criterion
     */
    public function addHaving(Criterion $criterion)
    {
        $this->criteria->add(
            $criterion->setType('having')
        );
    }

    /**
     * Gets a collection of TableVO objects.
     *
     * @return \CypryRadu\AdvancedFilter\TableCollection
     */
    private function getTableCollection()
    {
        $tables = $this->config->tables();
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
        $fields = $this->config->fields();
        $collection = new FieldCollection();

        foreach ($fields as $fieldKey => $fieldMeta) {
            $collection->add(new FieldVO($fieldKey, $fieldMeta));
        }

        return $collection;
    }

    /**
     * Builds the QueryBuilder based on the information in the Config and Criteria.
     *
     * @return \CypryRadu\AdvancedFilter\QueryBuilder\QueryBuilderInterface
     */
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
