<?php

namespace CypryRadu\AdvancedFilter;

use CypryRadu\AdvancedFilter\QueryBuilder\QueryBuilderInterface;

/**
 * The factory class for creating new Filter
 *
 * @author Ciprian Radu <cypryradu@gmail.com>
 */
class FilterFactory
{
    private $queryBuilder;

    /**
     * __construct
     *
     * @param mixed $db
     * @param string 'Dbal'|'ZendDb' $adapter
     * @throws InvalidArgumentException If there is an invalid adapter
     */
    public function __construct($db, $adapter = 'Dbal')
    {
        if (!in_array($adapter, array('Dbal', 'ZendDb'))) {
            throw new \InvalidArgumentException($adapter . ' adapter is not supported!');
        }

        $adapterClass = '\\CypryRadu\\AdvancedFilter\\QueryBuilder\\' . $adapter . 'QueryBuilder';
        $this->queryBuilder = new $adapterClass($db);
    }

    /**
     * Sets the query builder
     *
     * @param QueryBuilderInterface $queryBuilder
     * @access public
     * @return $this
     */
    public function setQueryBuilder(QueryBuilderInterface $queryBuilder)
    {
        $this->queryBuilder = $queryBuilder;

        return $this;
    }

    /**
     * Create the Filter object 
     * 
     * @return \CypryRadu\AdvancedFilter
     */
    public function create()
    {
        return new Filter($this->queryBuilder);
    }
}
