<?php

namespace CypryRadu\AdvancedFilter;

use Doctrine\Common\Collections\ArrayCollection;
use CypryRadu\AdvancedFilter\ValueObject\TableVO;

/**
 * Holds and manipulates a collection of TableVO objects.
 *
 * @author Ciprian Radu <cypryradu@gmail.com>
 */
class TableCollection extends ArrayCollection
{
    /**
     * Adds a new TableVO to the collection.
     *
     * @param \CypryRadu\AdvancedFilter\ValueObject\TableVO $table
     */
    public function add($table)
    {
        parent::set($table->getKey(), $table);
    }
}
