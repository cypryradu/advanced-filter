<?php
use CypryRadu\AdvancedFilter\ValueObject\Field;

class FieldTest extends PHPUnit_Framework_TestCase
{    
    public function testGetFieldNameNoDbField()
    {
        $field = new Field('recruitment_office', array(
            'table_alias' => 'ro',
        ));
        
        $this->assertEquals($field->getFieldName(), 'ro.`recruitment_office`');
    }
    
}
