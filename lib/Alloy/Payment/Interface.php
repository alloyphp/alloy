<?php
/**
 * Interface for processing payments online
 *
 * @package Alloy Framework
 * @link http://alloyframework.com
 */
interface Alloy_Payment_Interface
{
	/**
	 *	Set merchant account information
	 */
	public function setUsername($username);
	public function setPassword($password);
	public function setMerchantKey($key);
	public function setTesting($testing); // boolean true/false
	
	/**
	 *	Set customer info
	 */
	public function setCustomerId($id);
	public function setCustomerIp($ip); // IP Address
	public function setCustomerTaxId($taxid); // Tax ID, SSN, etc
	public function setFirstName($firstname);
	public function setLastName($lastname);
	public function setCompany($company);
	public function setAddress($address);
	public function setAddress2($address2);
	public function setCity($city);
	public function setState($state);
	public function setPostalCode($postalcode);
	public function setCountry($country);
	public function setEmail($email);
	public function setPhone($number);
	public function setFax($number);
	
	/**
	 *	Set payment method info
	 */
	public function setMethod($method);
	public function setProcessType($type); // AUTH_CAPTURE, AUTH_ONLY, VOID, etc.
	public function setCardType($type);
	public function setCardNumber($number);
	public function setCardExpiration($month, $year);
	public function setCardCode($code); // 3-4 digit number on card
	
	/**
	 *	Set actual payment info
	 */
	public function setAmount($amount);
	public function setCurrencyCode($code); // USD, EUR, etc.
	public function setInvoiceNumber($invoice);
	public function setRecurring($recurring); // boolean true/false
	public function setDescription($description);
	public function setTransactionId($id);
	
	/**
	 *	Merchant response info
	 */
	public function process(); // boolean true/false
	public function getResponseCode();
	public function getResponseReason();
	public function getResponseField($field);
	public function getRawRespose(); // raw response string
	public function getTransactionId();
	public function getAVSResultCode();
	public function getCardCodeResponse();
	public function getCAVVResponseCode(); // Secure Code, Verified by Visa, etc. CAV = Cardholder Authentication Verification
}