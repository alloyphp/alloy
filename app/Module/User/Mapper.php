<?php
class Module_User_Mapper extends Alloy_Mapper_Module
{
	// Table
	protected $source = "users";
	
	// Fields
	public $id = array('type' => 'int', 'primary' => true);
	public $username = array('type' => 'text', 'required' => true);
	public $password = array('type' => 'password', 'required' => true);
	public $email = array('type' => 'text', 'required' => true);
	public $date_created = array('type' => 'datetime');
	
	// Custom entity class
	protected $_entityClass = 'Module_User_Entity';
}