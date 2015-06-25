<?php

namespace CypryRadu\AdvancedFilter;

use Doctrine\Common\Collections\ArrayCollection;

class TableCollection extends ArrayCollection
{
    public function add($table)
    {
        parent::set($table->getKey(), $table);
    }
}
