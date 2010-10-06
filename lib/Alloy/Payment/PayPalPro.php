<?php
namespace Alloy\Payment;

/**
 * PayPal Pro Payment Processing Class
 *
 * @package Alloy
 * @link http://alloyframework.com/
 * @license http://www.opensource.org/licenses/bsd-license.php
 */
class PayPalPro implements AdapterInterface
{
	protected $fields = array();
	protected $responseString;
	protected $response = array();
	protected $responseUrl; // Used internally, do not set
	protected $url = "https://api-3t.paypal.com/nvp"; // live
	protected $urlTesting = "https://api-3t.sandbox.paypal.com/nvp"; // sandbox
	protected $testing = false;
	
	/**
	 *	Constants for gateway options
	 */
	const TYPE_AUTH_CAPTURE = 'DoDirectPayment';
	const TYPE_AUTH_ONLY = 'DoAuthorization';
	const TYPE_CAPTURE_ONLY = 'DoCapture';
	const TYPE_CREDIT = 'CREDIT';
	const TYPE_PRIOR_AUTH_CAPTURE = 'DoCapture';
	const TYPE_VOID = 'DoVoid';
	
	const PAYMENT_TYPE_CREDITCARD = 'CC';
	const PAYMNET_TYPE_ECHECK = null;
	
	
	/**
	 *	Contructor function setup
	 */
	public function __construct()
	{
		// Set version of the API to use
		$this->setField('VERSION', '50.0');
		
		// Credit Card transaction
		$this->setField('METHOD', 'DoDirectPayment');
		$this->setField('PAYMENTACTION', 'Sale');
		
		// Set the customer IP address
		$this->setCustomerIp($_SERVER['REMOTE_ADDR']);
	}
	
	
	/**
	 *	===============================================================================
	 *	Set URLs to override defaults set above
	 *	===============================================================================
	 */
	public function setUrl($url)
	{
		$this->url = $url;
		return true;
	}
	
	public function setTesting($testing, $testingUrl = null)
	{
		$this->testing = (bool) $testing;
		
		// Set testing URL if given
		if($testingUrl !== null) {
			$this->urlTesting = $testingUrl;
		}
		
		return true;
	}
	
	/**
	 *	===============================================================================
	 *	Set merchant account information
	 *	===============================================================================
	 */
	public function setUsername($username)
	{
		return $this->setField('USER', $username);
	}
	
	public function setPassword($password)
	{
		return $this->setField('PWD', $password);
	}
	
	public function setMerchantKey($key)
	{
		return $this->setField('SIGNATURE', $key);
	}
	
	/**
	 *	===============================================================================
	 *	Set customer info
	 *	===============================================================================
	 */
	public function setCustomerId($id)
	{
		return $this->setField('x', $id);
	}
	
	public function setCustomerIp($ip)
	{
		return $this->setField('IPADDRESS', $ip);
	}
	
	public function setCustomerTaxId($taxid)
	{
		return $this->setField('x', $taxid);
	}
	
	public function setFirstName($firstname)
	{
		return $this->setField('FIRSTNAME', $firstname);
	}
	
	public function setLastName($lastname)
	{
		return $this->setField('LASTNAME', $lastname);
	}
	
	public function setCompany($company)
	{
		return $this->setField('BUSINESS', $company);
	}
	
	public function setAddress($address)
	{
		return $this->setField('STREET', $address);
	}
	
	public function setAddress2($address2)
	{
		return $this->setField('STREET2', $address2);
	}
	
	public function setCity($city)
	{
		return $this->setField('CITY', $city);
	}
	
	public function setState($state)
	{
		return $this->setField('STATE', $state);
	}
	
	public function setPostalCode($postalCode)
	{
		return $this->setField('ZIP', $postalCode);
	}
	
	public function setCountry($country)
	{
		return $this->setField('COUNTRYCODE', $country);
	}
	
	public function setEmail($email)
	{
		return $this->setField('EMAIL', $email);
	}
	
	public function setPhone($number)
	{
		return $this->setField('PHONENUM', $number);
	}
	
	public function setFax($number)
	{
		return $this->setField('FAXNUM', $number);
	}
	
	// ===== SHIPPING INFORMATION (OPTIONAL) ==========================================
	
	public function setShippingFirstName($firstname)
	{
		return $this->setField('SHIPTONAME', $firstname);
	}
	
	public function setShippingLastName($lastname)
	{
		return $this->setField('SHIPTONAME', $this->fields['SHIPTONAME'] . ' ' . $lastname);
	}
	
	public function setShippingAddress($address)
	{
		return $this->setField('SHIPTOSTREET', $address);
	}
	
	public function setShippingAddress2($address2)
	{
		return $this->setField('SHIPTOSTREET2', $address2);
	}
	
	public function setShippingCity($city)
	{
		return $this->setField('SHIPTOCITY', $city);
	}
	
	public function setShippingState($state)
	{
		return $this->setField('SHIPTOSTATE', $state);
	}
	
	public function setShippingPostalCode($postalCode)
	{
		return $this->setField('SHIPTOZIP', $postalCode);
	}
	
	public function setShippingCountry($country)
	{
		return $this->setField('SHIPTOCOUNTRYCODE', $country);
	}
	
	public function setShippingPhone($number)
	{
		return $this->setField('SHIPTOPHONENUM', $number);
	}
	
	/**
	 *	===============================================================================
	 *	Set payment method info
	 *	===============================================================================
	 */
	public function setMethod($method)
	{
		return $this->setField('METHOD', $method);
	}
	
	public function setProcessType($type)
	{
		return $this->setField('PAYMENTACTION', $type);
	}
	
	public function setCardType($type)
	{
		return $this->setField('CREDITCARDTYPE', $type);
	}
	
	public function setCardNumber($number)
	{
		return $this->setField('ACCT', $number);
	}
	
	public function setCardExpiration($month, $year)
	{
		$month = (strlen($month) == 1) ? '0' . $month : $month;
		return $this->setField('EXPDATE', $month . $year); // MMYYYY Format
	}
	
	public function setCardCode($code)
	{
		return $this->setField('CVV2', $code);
	}
	
	/**
	 *	===============================================================================
	 *	Set actual payment info
	 *	===============================================================================
	 */
	public function setAmount($amount)
	{
		return $this->setField('AMT', $amount);
	}
	
	public function setAmountSubtotal($amount)
	{
		return $this->setField('ITEMAMT', $amount);
	}
	
	public function setAmountShipping($amount)
	{
		return $this->setField('SHIPPINGAMT', $amount);
	}
	
	public function setAmountTax($amount)
	{
		return $this->setField('TAXAMT', $amount);
	}
	
	public function setCurrencyCode($code)
	{
		return $this->setField('CURRENCYCODE', $code);
	}
	
	public function setInvoiceNumber($invoice)
	{
		return $this->setField('INVNUM', $invoice);
	}
	
	public function setRecurring($recurring)
	{
		return $this->setField('RECURRING', ((bool) $recurring ? 'TRUE' : 'FALSE'));
	}
	
	public function setDescription($description)
	{
		return $this->setField('DESC', substr($description, 0, 255));
	}
	
	public function setTransactionId($id)
	{
		return $this->setField('TRANSACTIONID', $id);
	}
	
	// == Extra functions for PayPal ==================================================
	
	public function setNote($note)
	{
		return $this->setField('NOTE', $note);
	}
	
	public function setAuthorizationId($id)
	{
		return $this->setField('AUTHORIZATIONID', $id);
	}
	
	public function setParentTransactionId($id)
	{
		return $this->setField('PARENTTRANSACTIONID', $id);
	}
	
	public function setRecieptId($id)
	{
		return $this->setField('RECEIPTID', $id);
	}
	
	public function setRefundType($type)
	{
		return $this->setField('REFUNDTYPE', $type);
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
		$processUrl = ($this->testing) ? $this->urlTesting : $this->url;
		$this->responseUrl = $processUrl;
		
		// Contruct fields into string to send as POST data
		foreach($this->fields as $key => $value) {
			// Only send non-empty values
			if(!empty($value)) {
				$this->fieldString .= $key . "=" . urlencode($value) . "&";
			}
		}
		$this->fieldString = rtrim($this->fieldString, "& ");
		
		//setting the curl parameters.
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $processUrl);
		curl_setopt($ch, CURLOPT_VERBOSE, 1);

		//turning off the server and peer verification(TrustManager Concept).
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch, CURLOPT_POST, 1);

		// Set the POST fields from the constructed string
		curl_setopt($ch,CURLOPT_POSTFIELDS, $this->fieldString);

		// Get server response
		$response = curl_exec($ch);

		// Convert NVPResponse to an associative array
		$this->response = $this->deformatNVP($response);

		if(curl_errno($ch)) {
			// moving to display page to display curl errors
			$this->response['curl_error_no'] = curl_errno($ch) ;
			$this->response['L_LONGMESSAGE0'] = curl_error($ch);
		} else {
			//closing the curl
			curl_close($ch);
		}
		

		// Return boolean true/false
        // PayPal result can be "SuccessWithWarning" or "Success"
        // @todo Handle "SuccessWithWarning" better to store the warning messages
		return (isset($this->response['ACK']) && strpos($this->response['ACK'], "Success" === 0) ? true : false;
	}
	
	
	/**
	 * Function to convert server response into associative array
	 * From PayPal's SDK download
	 */
	protected function deformatNVP($nvpstr)
	{
		$intial = 0;
	 	$nvpArray = array();

		while(strlen($nvpstr)){
			//postion of Key
			$keypos= strpos($nvpstr, '=');
			//position of value
			$valuepos = strpos($nvpstr, '&') ? strpos($nvpstr, '&'): strlen($nvpstr);

			/*getting the Key and Value values and storing in a Associative Array*/
			$keyval=substr($nvpstr, $intial, $keypos);
			$valval=substr($nvpstr, $keypos+1, $valuepos-$keypos-1);
			//decoding the respose
			$nvpArray[urldecode($keyval)] = urldecode($valval);
			$nvpstr=substr($nvpstr, $valuepos+1, strlen($nvpstr));
	     }
		return $nvpArray;
	}

	public function getResponseCode()
	{
		return isset($this->response['L_ERRORCODE0']) ? $this->response['L_ERRORCODE0'] : false;
	}
	
	public function getResponseReason()
	{
		return isset($this->response['L_LONGMESSAGE0']) ? $this->response['L_LONGMESSAGE0'] : false;
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
		return isset($this->response['TRANSACTIONID']) ? $this->response['TRANSACTIONID'] : false;
	}
	
	public function getAVSResultCode()
	{
		return isset($this->response['AVSCODE']) ? $this->response['AVSCODE'] : false;
	}
	
	public function getCardCodeResponse()
	{
		return isset($this->response['x']) ? $this->response['x'] : false;
	}
	
	public function getCAVVResponseCode()
	{
		return isset($this->response['CVV2MATCH']) ? $this->response['CVV2MATCH'] : false;
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
		echo "<p>Response From: " . $this->responseUrl . "</p>";
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
