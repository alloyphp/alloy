<?php
/**
 * Post Comment
 * @todo implement 'BelongsTo' relation for linking back to blog post object
 *
 * @package Spot
 * @link http://spot.os.ly
 */
class Entity_Post_Comment extends \Spot\Entity
{
    protected static $_datasource = 'test_post_comments';
    
    public static function fields()
    {
        return array(
            'id' => array('type' => 'int', 'primary' => true, 'serial' => true),
            'post_id' => array('type' => 'int', 'index' => true, 'required' => true),
            'name' => array('type' => 'string', 'required' => true),
            'email' => array('type' => 'string', 'required' => true),
            'body' => array('type' => 'text', 'required' => true),
            'date_created' => array('type' => 'datetime')
        );
    }
}