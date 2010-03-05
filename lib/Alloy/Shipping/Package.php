<?php
/**
 * Shipping Package
 *
 * Holds package details for more presice calculations when adapters can support it
 * 
 * @package Alloy Framework
 * @link http://alloyframework.com
 */
class Alloy_Shipping_Package
{
	// Weight
	protected $weight;
	protected $weightUnit = 'lbs';
	
	// Dimensions
	protected $height;
	protected $width;
	protected $length;
	protected $isOversize = false;
	
	// Other
	protected $value; // Declared value of package
	
	
	// Weight
	public function setWeight($value, $unit = 'lbs')
	{
		$this->weight = $value;
		if($unit) {
			$this->setWeightUnit($unit);
		}
	}
	public function setWeightUnit($value)
	{
		$this->weightUnit = $value;
	}
	// Getters
	public function getWeight()
	{
		return $this->weight;
	}
	public function getWeightUnit()
	{
		return $this->weightUnit;
	}
	
	
	// Dimensions
	public function setHeight($value)
	{
		$this->height = (float) $value;
	}
	public function setWidth($value)
	{
		$this->width = (float) $value;
	}
	public function setLength($value)
	{
		$this->length = (float) $value;
	}
	public function setOversize($value)
	{
		$this->isOversize = (bool) $value;
	}
	// Getters
	public function getHeight()
	{
		return $this->height;
	}
	public function getWidth()
	{
		return $this->width;
	}
	public function getLength()
	{
		return $this->length;
	}
	public function getOversize()
	{
		return $this->isOversize;
	}
	
	
	// Other
	public function setValue($value)
	{
		$this->value = (float) $value;
	}
	// Getters
	public function getValue()
	{
		return $this->value;
	}
}