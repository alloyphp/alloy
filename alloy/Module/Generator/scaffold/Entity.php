<?php
namespace Module\{$generator.namespace};
use Spot;

class Entity extends Spot\Entity
{
    protected static $_datasource = '{$generator.name_table}';

    public static function fields()
    {
        return array(
            'id' => array('type' => 'int', 'primary' => true, 'serial' => true),
            {$generator.field_string}
            'date_created' => array('type' => 'datetime', 'default' => new \DateTime()),
            'date_modified' => array('type' => 'datetime', 'default' => new \DateTime())
        );
    }
}