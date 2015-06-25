<?php
namespace CypryRadu\AdvancedFilter;

use Doctrine\DBAL\Query\QueryBuilder;

class Criteria
{
    private $criteria = array();
    
    public function add(Criterion $criterion)
    {
        $this->criteria[] = $criterion;
    }

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
