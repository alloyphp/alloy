<?php
/**
 * Authorize.net Payment Processing Class
 *
 * @package Alloy Framework
 * @link http://alloyframework.com
 */
class Alloy_Payment_AuthorizeNet implements Alloy_Payment_Interface
{
	protected $fields = array();
	protected $responseString;
	protected $response = array();
	protected $url = "https://secure.authorize.net/gateway/transact.dll";
	
	/**
	 *	Constants for gateway options
	 */
	const TYPE_AUTH_CAPTURE = 'AUTH_CAPTURE';
	const TYPE_AUTH_ONLY = 'AUTH_ONLY';
	const TYPE_CAPTURE_ONLY = 'CAPTURE_ONLY';
	const TYPE_CREDIT = 'CREDIT';
	const TYPE_PRIOR_AUTH_CAPTURE = 'PRIOR_AUTH_CAPTURE';
	const TYPE_VOID = 'VOID';
	
	const PAYMENT_TYPE_CREDITCARD = 'CC';
	const PAYMNET_TYPE_ECHECK = 'ECHECK';
	
	
	/**
	 *	Contructor function setup
	 */
	public function __construct()
	{
		// These fields must be set for this class to work
		$this->setField('x_delim_data', 'TRUE');
		$this->setField('x_delim_char', '|');     
		$this->setField('x_encap_char', '');
		$this->setField('x_relay_response', 'FALSE');
		
		// This class is setup to use version "3.1" of the API
		$this->setField('x_version', '3.1');
		
		// Credit Card transaction
		$this->setField('x_method', 'CC');
	}
	
	
	/**
	 *	===============================================================================
	 *	Set merchant account information
	 *	===============================================================================
	 */
	public function setUsername($username)
	{
		return $this->setField('x_login', $username);
	}
	
	public function setPassword($password)
	{
		return $this->setField('x_password', $password);
	}
	
	public function setMerchantKey($key)
	{
		return $this->setField('x_tran_key', $key);
	}
	
	public function setTesting($testing)
	{
		return $this->setField('x_test_request', ((bool) $testing ? 'TRUE' : 'FALSE'));
	}
	
	/**
	 *	===============================================================================
	 *	Set customer info
	 *	===============================================================================
	 */
	public function setCustomerId($id)
	{
		return $this->setField('x_cust_id', $id);
	}
	
	public function setCustomerIp($ip)
	{
		return $this->setField('x_customer_ip', $ip);
	}
	
	public function setCustomerTaxId($taxid)
	{
		return $this->setField('x_customer_tax_id', $taxid);
	}
	
	public function setFirstName($firstname)
	{
		return $this->setField('x_first_name', $firstname);
	}
	
	public function setLastName($lastname)
	{
		return $this->setField('x_last_name', $lastname);
	}
	
	public function setCompany($company)
	{
		return $this->setField('x_company', $company);
	}
	
	public function setAddress($address)
	{
		return $this->setField('x_address', $address);
	}
	
	public function setAddress2($address2)
	{
		return $this->setField('x_address', $this->fields['x_address'] . ' ' . $address2);
	}
	
	public function setCity($city)
	{
		return $this->setField('x_city', $city);
	}
	
	public function setState($state)
	{
		return $this->setField('x_state', $state);
	}
	
	public function setPostalCode($postalCode)
	{
		return $this->setField('x_zip', $postalCode);
	}
	
	public function setCountry($country)
	{
		return $this->setField('x_country', $country);
	}
	
	public function setEmail($email)
	{
		return $this->setField('x_email', $email);
	}
	
	public function setPhone($number)
	{
		return $this->setField('x_phone', $number);
	}
	
	public function setFax($number)
	{
		return $this->setField('x_fax', $number);
	}
	
	/**
	 *	===============================================================================
	 *	Set payment method info
	 *	===============================================================================
	 */
	public function setMethod($method)
	{
		return $this->setField('x_method', $method);
	}
	
	public function setProcessType($type)
	{
		return $this->setField('x_type', $type);
	}
	
	public function setCardType($type)
	{
		return true; // Not implemented yet
	}
	
	public function setCardNumber($number)
	{
		return $this->setField('x_card_num', $number);
	}
	
	public function setCardExpiration($month, $year)
	{
		$month = (strlen($month) == 1) ? '0' . $month : $month;
		return $this->setField('x_exp_date', $month . '/' . $year);
	}
	
	public function setCardCode($code)
	{
		return $this->setField('x_card_code', $code);
	}
	
	/**
	 *	===============================================================================
	 *	Set actual payment info
	 *	===============================================================================
	 */
	public function setAmount($amount)
	{
		return $this->setField('x_amount', $amount);
	}
	
	public function setCurrencyCode($code)
	{
		return $this->setField('x_currency_code', $code);
	}
	
	public function setInvoiceNumber($invoice)
	{
		return $this->setField('x_invoice_num', $invoice);
	}
	
	public function setRecurring($recurring)
	{
		return $this->setField('x_recurring_billing', ((bool) $recurring ? 'TRUE' : 'FALSE'));
	}
	
	public function setDescription($description)
	{
		return $this->setField('x_description', substr($description, 0, 255));
	}
	
	public function setTransactionId($id)
	{
		return $this->setField('x_trans_id', $id);
	}
	
	/**
	 *	===============================================================================
	 *	Merchant response info
	 *	===============================================================================
	 */
	 
	/**
	 *	Send the request to the merchant gateway
	 */
	public function process()
	{
		// This function actually processes the payment.  This function will 
		// load the $response array with all the returned information.  The return
		// values for the function are:
		// 1 - Approved
		// 2 - Declined
		// 3 - Error
 
		// construct the fields string to pass to authorize.net
		foreach( $this->fields as $key => $value ) 
		{
			$this->field_string .= $key . "=" . urlencode($value) . "&";
		}
	
		// execute the HTTPS post via CURL
		$ch = curl_init($this->url); 
		curl_setopt($ch, CURLOPT_HEADER, 0); 
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		
		// Set the location of the CA-bundle
		$caBundle = FW_ROOT . 'application\curl-ca-bundle.crt';
		curl_setopt($ch, CURLOPT_CAPATH, $caBundle);
		
		curl_setopt($ch, CURLOPT_POSTFIELDS, rtrim( $this->field_string, "& " )); 
		$this->responseString = urldecode(curl_exec($ch)); 
	
		// Check for CURL error
		if (curl_errno($ch)) {
			$this->response['Response Reason Text'] = curl_error($ch);
			return 3;
		}
		else
		{
			curl_close ($ch);
		}

		// load a temporary array with the values returned from authorize.net
		$temp_values = explode('|', $this->responseString);

		// load a temporary array with the keys corresponding to the values 
		// returned from authorize.net (taken from AIM documentation)
		$temp_keys= array ( 
			"Response Code", "Response Subcode", "Response Reason Code", "Response Reason Text",
			"Approval Code", "AVS Result Code", "Transaction ID", "Invoice Number", "Description",
			"Amount", "Method", "Transaction Type", "Customer ID", "Cardholder First Name",
			"Cardholder Last Name", "Company", "Billing Address", "City", "State",
			"Zip", "Country", "Phone", "Fax", "Email", "Ship to First Name", "Ship to Last Name",
			"Ship to Company", "Ship to Address", "Ship to City", "Ship to State",
			"Ship to Zip", "Ship to Country", "Tax Amount", "Duty Amount", "Freight Amount",
			"Tax Exempt Flag", "PO Number", "MD5 Hash", "Card Code (CVV2/CVC2/CID) Response Code",
			"Cardholder Authentication Verification Value (CAVV) Response Code"
		);

		// add additional keys for reserved fields and merchant defined fields
		for ($i=0; $i<=27; $i++) {
			array_push($temp_keys, 'Reserved Field '.$i);
		}
		$i=0;
		while (sizeof($temp_keys) < sizeof($temp_values)) {
			array_push($temp_keys, 'Merchant Defined Field '.$i);
			$i++;
		}

		// combine the keys and values arrays into the $response array.  This
		// can be done with the array_combine() function instead if you are using
		// php 5.
		//$this->response = array_combine($temp_keys, $temp_values);
		for ($i=0; $i<sizeof($temp_values);$i++) {
			$this->response["$temp_keys[$i]"] = $temp_values[$i];
		}
		

		// Return boolean true/false
		return ($this->response['Response Code'] == 1) ? true : false;
	}

	public function getResponseCode()
	{
		return $this->response['Response Code'];
	}
	
	public function getResponseReason()
	{
		return $this->response['Response Reason Text'];
	}

	public function getResponseField($field)
	{
		if(array_key_exists($field, $this->response)) {
			return $this->response[$field];
		} else {
			return false;
		}
	}
	
	public function getRawRespose()
	{
		return $this->response;
	}
	
	public function getTransactionId()
	{
		return $this->response['Transaction ID'];
	}
	
	public function getAVSResultCode()
	{
		return $this->response['AVS Result Code'];
	}
	
	public function getCardCodeResponse()
	{
		return $this->response['Card Code (CVV2/CVC2/CID) Response Code'];
	}
	
	public function getCAVVResponseCode()
	{
		return $this->response['Cardholder Authentication Verification Value (CAVV) Response Code'];
	}
	
	
	
	/**
	 *	===============================================================================
	 *	Internal functions
	 *	===============================================================================
	 */
	
	/**
	 *	Set field with a value
	 */
	protected function setField($name, $value)
	{
		$this->fields[$name] = $value;
	}
	
	/**
	 *	Used for debugging, dumps all set fields
	 */
	public function dumpFields()
	{
		echo "<h3>" . __FUNCTION__ . "() Output:</h3>";
		echo "<table width=\"95%\" border=\"1\" cellpadding=\"2\" cellspacing=\"0\">
            <tr>
               <td bgcolor=\"black\"><b><font color=\"white\">Field Name</font></b></td>
               <td bgcolor=\"black\"><b><font color=\"white\">Value</font></b></td>
            </tr>"; 
            
		foreach ($this->fields as $key => $value) {
			echo "<tr><td>$key</td><td>".urldecode($value)."&nbsp;</td></tr>";
		}
 
		echo "</table><br>"; 
	}


	/**
	 *	Used for debugging, dumps all fields received in the response from merchant gateway
	 */
	public function dumpResponse()
	{

		// Used for debuggin, this function will output all the response field
		// names and the values returned for the payment submission.  This should
		// be called AFTER the process() function has been called to view details
		// about authorize.net's response.
		
		echo "<h3>" . __FUNCTION__ . "() Output:</h3>";
		echo "<table width=\"95%\" border=\"1\" cellpadding=\"2\" cellspacing=\"0\">
            <tr>
               <td bgcolor=\"black\"><b><font color=\"white\">Index&nbsp;</font></b></td>
               <td bgcolor=\"black\"><b><font color=\"white\">Field Name</font></b></td>
               <td bgcolor=\"black\"><b><font color=\"white\">Value</font></b></td>
            </tr>";
            
		$i = 0;
		foreach ($this->response as $key => $value)
		{
			echo "<tr>
                  <td valign=\"top\" align=\"center\">$i</td>
                  <td valign=\"top\">$key</td>
                  <td valign=\"top\">$value&nbsp;</td>
			</tr>";
			$i++;
		}
		echo "</table><br>";
	}
}