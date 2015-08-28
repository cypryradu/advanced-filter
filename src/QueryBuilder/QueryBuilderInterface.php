<?php

namespace CypryRadu\AdvancedFilter\QueryBuilder;

interface QueryBuilderInterface
{
    public function getSql(); // converts the builder to SQL statement
    public function from($from, $alias); // sets the FROM clause
    public function select($select = null); // SELECT fields resetting all previous ones
    public function addSelect($selectFieldExpr); // add more SELECT fields
    public function join($fromAlias, $join, $alias, $condition = null); // adds a JOIN
    public function innerJoin($fromAlias, $join, $alias, $condition = null); // adds an INNER JOIN
    public function leftJoin($fromAlias, $join, $alias, $condition = null); // adds a LEFT JOIN
    public function rightJoin($fromAlias, $join, $alias, $condition = null); // adds a RIGHT JOIN
    public function where($predicates); // sets WHERE clause, replaces any previously defined WHERE conditions
    public function andWhere($where); // adds an OR condition to the WHERE clause
    public function orWhere($where); // adds an OR condition to the WHERE clause
    public function createPositionalParameter($value, $type = \PDO::PARAM_STR);
    public function getOriginal(); // get the original query builder behind
}
