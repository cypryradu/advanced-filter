<?php
namespace CypryRadu\AdvancedFilter\ValueObject;

/**
* Encapsulates the data for a single fields
*
* @author Ciprian Radu <cypryradu@gmail.com>
*/
class Field 
{
    /**
     * @var string
     */
    private $fieldKey;

    /**
     * @var array
     */
    private $metaData;
    
    /**
     * Constructor
     *
     * @param string $fieldKey
     * @param array $metaData
     *
     */
    public function __construct($fieldKey, array $metaData)
    {
        $this->fieldKey = $fieldKey;
        $this->metaData = $metaData;
    }
    
    /**
     * Gets the field's key
     *
     * @return string
     */
    public function getKey()
    {
        return $this->fieldKey;
    }
    
    /**
     * Gets the the field's name in the database
     *
     * @return string
     */
    public function getDbField()
    {
        return (!empty($this->metaData['db_field']) 
            ? $this->metaData['db_field'] 
            : $this->fieldKey
        );
    }
    
    /**
     * Gets the tables to be used by this field
     *
     * @return array
     */
    public function getUseTables()
    {
        if (!isset($this->metaData['use_tables']) || !is_array($this->metaData['use_tables'])) {
            return array();
        }
        
        return $this->metaData['use_tables'];
    }
    
    /**
     * Gets the field's table alias
     *
     * @return string
     */
    public function getTableAlias()
    {
        return (!empty($this->metaData['table_alias']) ? $this->metaData['table_alias'] . '.' : '');
    }
    
    /**
     * Gets the field's table alias
     *
     * @return string
     */
    public function getFieldName()
    {
        return $this->getTableAlias() . '`' . $this->getDbField() . '`';
    }
}
