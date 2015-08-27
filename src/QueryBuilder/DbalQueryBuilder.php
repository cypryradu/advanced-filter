<?php

namespace CypryRadu\AdvancedFilter\QueryBuilder;

use Doctrine\DBAL\Query\QueryBuilder as DoctrineQB;

class DbalQueryBuilder extends DoctrineQB implements QueryBuilderInterface
{
    public function getOriginal()
    {
        return $this;
    }
}
