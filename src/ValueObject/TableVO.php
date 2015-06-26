<?php

namespace CypryRadu\AdvancedFilter\ValueObject;

/**
 * Encapsulates the data for a single table.
 *
 * This class is immutable
 *
 * @author Ciprian Radu <cypryradu@gmail.com>
 */
class TableVO
{
    /**
     * @var string
     */
    private $tableKey;

    /**
     * @var string
     */
    private $tableName;

    /**
     * @var string
     */
    private $tableAlias;

    /**
     * @var array
     */
    private $metaData;

    /**
     * @var string
     */
    private $joinType;

    /**
     * @var string
     */
    private $joinOn;

    /**
     * Constructor.
     *
     * @param string $tableKey
     * @param array  $metaData
     */
    public function __construct($tableKey, array $metaData)
    {
        list($tableName, $tableAlias) = array_reverse(explode('.', $tableKey));

        $this->tableKey = $tableKey;
        $this->tableName = $tableName;
        $this->tableAlias = $tableAlias;
        $this->metaData = $metaData;
        $this->joinType = array_shift($metaData);
        $this->joinOn = array_shift($metaData);
    }

    /**
     * Gets the tables's key.
     *
     * @return string
     */
    public function getKey()
    {
        return $this->tableKey;
    }

    /**
     * Gets the tables's name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->tableName;
    }

    /**
     * Gets the tables's alias.
     *
     * @return string
     */
    public function getAlias()
    {
        return $this->tableAlias;
    }

    /**
     * Gets the tables's join type with the other tables.
     *
     * @return string
     */
    public function getJoinType()
    {
        return $this->joinType;
    }

    /**
     * Gets the tables's join ON condition.
     *
     * @return string
     */
    public function getJoinOn()
    {
        return $this->joinOn;
    }
}
