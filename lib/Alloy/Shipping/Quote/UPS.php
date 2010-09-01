<?php
namespace Alloy\Shipping;

/**
 * UPS Rate Quote
 * 
 * UPS Developer Documentation
 * @link http://inchoo.net/wp-content/uploads/2008/10/ratesandservicehtml.pdf
 * 
 * @package Alloy
 * @link http://alloyframework.com/
 * @license http://www.opensource.org/licenses/bsd-license.php
 */
class Quote_UPS implements Quote_AdapterInterface
{
	protected $fields = array();
	protected $responseCode;
	protected $responseString;
	protected $responseError = false;
	protected $responseErrorText;
	protected $response = null;
	protected $url = "http://www.ups.com/using/services/rave/qcostcgi.cgi";
	
	protected $packages = array();
	
	
	/**
	 *	Contructor function setup
	 */
	public function __construct()
	{
		// Setup Required Variables
		$this->setField('accept_UPS_license_agreement', 'yes');
		$this->setField('10_action', '3'); // 3 = return quote. Do not change.
		
		/*
		1DM == Next Day Air Early AM
		1DA == Next Day Air
		1DP == Next Day Air Saver
		2DM == 2nd Day Air Early AM
		2DA == 2nd Day Air
		3DS == 3 Day Select
		GND == Ground
		STD == Canada Standard
		XPR == Worldwide Express
		XDM == Worldwide Express Plus
		XPD == Worldwide Expedited
		*/
		$this->setField('13_product', 'GND');
	
		/*
		"Regular+Daily+Pickup"
		"On+Call+Air"
		"One+Time+Pickup"
		"Letter+Center"
		"Customer+Counter"
		*/
		$this->setField('47_rateChart', 'Regular Daily Pickup');
		
		/*
		00 - Customer Packaging
		01 - UPS Letter Envelope
		03 - UPS Tube
		21 - UPS Express Box
		24 - UPS Worldwide 25 kilo
		25 - UPS Worldwide 10 kilo
		*/
		$this->setField('48_container', '00');
		
		/*
		1 = Residential
		2 = Business
		*/
		$this->setField('49_residential', '1');
	}
	
	// Add package to current calculation total
	public function addPackage(Back40_Shipping_Package $package)
	{
		$this->packages[] = $package;
	}
	
	/**
	 *	===============================================================================
	 *	Credentials
	 *	===============================================================================
	 */
	public function setUsername($username)
	{
		//return $this->setField('', $username);
	}
	
	public function setPassword($password)
	{
		//return $this->setField('', $password);
	}
	
	public function setCustomerId($id)
	{
		//return $this->setField('', $id);
	}
	
	/**
	 *	===============================================================================
	 *	Origin
	 *	===============================================================================
	 */
	
	public function setOriginCity($value)
	{
		return $this->setField('origCity', $value);
	}
	
	public function setOriginState($value)
	{
		//return $this->setField('', $value);
	}
	
	public function setOriginPostalCode($value)
	{
		return $this->setField('15_origPostal', $value);
	}
	
	public function setOriginCountry($value)
	{
		return $this->setField('14_origCountry', $value);
	}
	
	/**
	 *	===============================================================================
	 *	Destination
	 *	===============================================================================
	 */
	
	public function setDestinationCity($value)
	{
		return $this->setField('20_destCity', $value);
	}
	
	public function setDestinationState($value)
	{
		//return $this->setField('', $value);
	}
	
	public function setDestinationPostalCode($value)
	{
		return $this->setField('19_destPostal', $value);
	}
	
	public function setDestinationCountry($value)
	{
		return $this->setField('22_destCountry', $value);
	}
	
	/**
	 *	===============================================================================
	 *	Package Info
	 *	===============================================================================
	 */
	public function setPackageOversize($value)
	{
		// Unused for now...
	}
	
	public function setPackageWeight($value)
	{
		$this->setField('23_weight', $value);
		//$this->setPackageClass('');
		return true;
	}
	
	public function setPackageClass($value)
	{
		//$this->setField('', $value);
		return true;
	}
	
	public function setPackageValue($value)
	{
		$this->setField('24_value', $value); // For Insurance
		return true;
	}
	
	/**
	 *	===============================================================================
	 *	Response info
	 *	===============================================================================
	 */
	 
	/**
	 *	Send the request
	 */
	public function getQuote()
	{
		$quote = 0;
		
		// Get separate quote for each package
		if(count($this->packages) > 0) {
			foreach($this->packages as $package) {
				// Set fields for each package
				$this->setPackageWeight($package->getWeight());
				$this->setPackageValue($package->getValue());
				
				// execute the HTTPS post via CURL
				$ch = curl_init($this->url); 
				curl_setopt($ch, CURLOPT_HEADER, 0); 
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
				
				// Set the location of the CA-bundle
				$caBundle = FW_ROOT . 'application\curl-ca-bundle.crt';
				curl_setopt($ch, CURLOPT_CAPATH, $caBundle);
				
				curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($this->fields)); 
				$this->responseString = curl_exec($ch); 
			
				// Check for CURL error
				if(curl_errno($ch)) {
					$this->responseError = true;
					$this->responseErrorText = curl_error($ch);
					return 3;
				} else {
					curl_close($ch);
				}
				
				// Parse response
				$this->response = explode('%', $this->responseString);
				$responseParamCount = count($this->response);
				$this->responseCode = substr($this->response[0], -1); // Last number on end of first response field... Wtf?
				$this->responseReason = $this->response[$responseParamCount-2];
				
				// Check response code for error indication
				if($this->responseCode > 4) { // 3 or 4 is good, 5 or 6 is error
					$quote += 0;
					$this->responseError = true;
					$this->responseErrorText = $this->response[1];
				} else {
					// Price quote is 3rd from last value
					$quote += (float) $this->response[$responseParamCount-3];
				}
			}
		}
		
		// Return boolean true/false
		return ($this->responseError) ? false : $quote;
	}

	public function getResponseCode()
	{
		return $this->responseCode;
	}
	
	public function getResponseReason()
	{
		return $this->responseReason;
	}
	
	public function getErrorText()
	{
		return $this->responseErrorText;
	}

	public function getResponseField($field)
	{
		return false;
	}
	
	public function getRawRespose()
	{
		return $this->responseString;
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
		$this->fields[$name] = trim($value);
	}
	
	/**
	 *	Used for debugging, dumps all set fields
	 */
	public function dumpRequest()
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
		foreach ($this->response as $key => $value) {
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
