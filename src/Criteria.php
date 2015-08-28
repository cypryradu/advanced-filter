<?php

namespace CypryRadu\AdvancedFilter;

use CypryRadu\AdvancedFilter\QueryBuilder\QueryBuilderInterface;

/**
 * The class that holds all the individual Criterion(s)
 * performing the build() call on each of them.
 *
 * @author Ciprian Radu <cypryradu@gmail.com>
 */
class Criteria
{
    /**
     * @var array
     */
    private $criteria = array();

    /**
     * add individual Criterions to this collection.
     *
     * @param \CypryRadu\AdvancedFilter\Criterion $criterion
     */
    public function add(Criterion $criterion)
    {
        $this->criteria[] = $criterion;
    }

    /**
     * Propagates the build call to each individual Criterion(s).
     *
     * @param \CypryRadu\AdvancedFilter\QueryBuilder\QueryBuilderInterface $builder
     * @param \CypryRadu\AdvancedFilter\TableCollection                    $tables
     * @param \CypryRadu\AdvancedFilter\TableCollection                    $usedTables
     * @param \CypryRadu\AdvancedFilter\FieldCollection                    $fields
     * @param array                                                        $columns
     */
    public function build(
        QueryBuilderInterface $builder,
        TableCollection $tables,
        TableCollection $usedTables,
        FieldCollection $fields,
        array $columns
    ) {
        foreach ($this->criteria as $criterion) {
            $criterion->build($builder, $tables, $usedTables, $fields, $columns);
        }
    }
}
