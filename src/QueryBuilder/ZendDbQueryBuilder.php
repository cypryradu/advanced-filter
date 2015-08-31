<?php

namespace CypryRadu\AdvancedFilter\QueryBuilder;

class ZendDbQueryBuilder implements QueryBuilderInterface
{
    protected $select;
    private $positionalParams = array();

    public function __construct($db)
    {
        $this->select = $db->select();
    }

    public function getSql()
    {
        return $this->select->__toString();
    }

    public function from($from, $alias)
    {
        $this->select->from(array(
            $alias => $from,
        ));

        return $this;
    }

    public function select($select = null)
    {
        $this->select->reset('columns');
        $this->select->columns($select);

        return $this;
    }

    public function addSelect($selectFieldExpr)
    {
        list($fieldExpr, $alias) = explode(' AS ', $selectFieldExpr);

        if (!empty($alias)) {
            $alias = trim($alias, "'");
            $this->select->columns(array($alias => $fieldExpr));
        } else {
            $this->select->columns(array($fieldExpr));
        }
    }

    public function join($fromAlias, $join, $alias, $condition = null)
    {
        $this->select->join(array(
            $alias => $join,
        ), $condition, array());

        return $this;
    }

    public function innerJoin($fromAlias, $join, $alias, $condition = null)
    {
        $this->select->joinInner(array(
            $alias => $join,
        ), $condition, array());

        return $this;
    }

    public function leftJoin($fromAlias, $join, $alias, $condition = null)
    {
        $this->select->joinLeft(array(
            $alias => $join,
        ), $condition, array());

        return $this;
    }

    public function rightJoin($fromAlias, $join, $alias, $condition = null)
    {
        $this->select->joinRight(array(
            $alias => $join,
        ), $condition, array());

        return $this;
    }

    public function where($predicates)
    {
        $this->select->reset('where');
        if ($param = array_shift($this->positionalParams)) {
            $this->select->where($predicates, $param);
        } else {
            $this->select->where($predicates);
        }

        return $this;
    }

    public function andWhere($where)
    {
        if ($param = array_shift($this->positionalParams)) {
            $this->select->where($where, $param);
        } else {
            $this->select->where($where);
        }

        return $this;
    }

    public function orWhere($where)
    {
        if ($param = array_shift($this->positionalParams)) {
            $this->select->orWhere($where, $param);
        } else {
            $this->select->orWhere($where);
        }

        return $this;
    }

    public function createPositionalParameter($value, $type = \PDO::PARAM_STR)
    {
        $this->positionalParams[] = $value;

        return '?';
    }

    public function getOriginal()
    {
        return $this->select;
    }
}
