<?php

namespace CypryRadu\AdvancedFilter;

use Doctrine\DBAL\Query\QueryBuilder;

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
     * @param \Doctrine\DBAL\Query\QueryBuilder         $builder
     * @param \CypryRadu\AdvancedFilter\TableCollection $tables
     * @param \CypryRadu\AdvancedFilter\TableCollection $usedTables
     * @param \CypryRadu\AdvancedFilter\FieldCollection $fields
     */
    public function build(
        QueryBuilder $builder,
        TableCollection $tables,
        TableCollection $usedTables,
        FieldCollection $fields
    ) {
        foreach ($this->criteria as $criterion) {
            $criterion->build($builder, $tables, $usedTables, $fields);
        }
    }
}
