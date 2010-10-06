<?php
namespace Alloy\Shipping;

/**
 * Shipping Rate Quote Interface
 * 
 * @package Alloy
 * @link http://alloyframework.com/
 * @license http://www.opensource.org/licenses/bsd-license.php
 */
interface Quote_AdapterInterface
{
	// Add package to current calculation total
	public function addPackage(Back40_Shipping_Package $package);
	
	/**
	 *	===============================================================================
	 *	Credentials
	 *	===============================================================================
	 */
	public function setUsername($username);
	public function setPassword($password);
	public function setCustomerId($id);
	
	/**
	 *	===============================================================================
	 *	Origin
	 *	===============================================================================
	 */
	public function setOriginCity($value);
	public function setOriginState($value);
	public function setOriginPostalCode($value);
	public function setOriginCountry($value);
	
	/**
	 *	===============================================================================
	 *	Destination
	 *	===============================================================================
	 */
	public function setDestinationCity($value);
	public function setDestinationState($value);
	public function setDestinationPostalCode($value);
	public function setDestinationCountry($value);
	
	/**
	 *	===============================================================================
	 *	Response info
	 *	===============================================================================
	 */
	public function getQuote();
	public function getResponseCode();
	public function getResponseReason();
	public function getErrorText();
	public function getResponseField($field);
	public function getRawRespose();

	/**
	 *	===============================================================================
	 *	Debugging
	 *	===============================================================================
	 */
	public function dumpRequest();
	public function dumpResponse();
}
