<?php

/**
 * Trans Union API Library
 *
 * https://github.com/PeterMartinez/Transunion-php
 *
 * @copyright @PeterMartinez on GitHub
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 *
 *
 * @author @PeterMartinez on GitHub
 * @version 1.0.0
 *
 */

namespace Transunion;

use Exception;

class Transunion {

    const api_version = '2.10';

    private $mode = '';
    private $base_url = '';
    private $transactionControl = array();
    private $certificate = array(); //key,crt,password

    //Convert Certificate p12 => PEM 
    //http://stackoverflow.com/questions/24363317/curl-cannot-connect-using-p12-certificate

    /**
     * Constructor function for all new Transunion instances
     *
     * Store Subscriber & Options
     *
     * @param array $transactionControl
     * 	$certificate = array();
     * 	$certificate['key'] 
     * 	$certificate['crt']
     * 	$certificate['password']
     * 	$transactionControl = array();
     * 	$transactionControl['userRefNumber']
     * 	$transactionControl['subscriber'] = array()
     * 		$transactionControl['subscriber']['industryCode']
     * 		$transactionControl['subscriber']['memberCode']
     * 		$transactionControl['subscriber']['inquirySubscriberPrefixCode']
     * 		$transactionControl['subscriber']['password']
     * 	$transactionControl['options'] = array()
     * 		$transactionControl['options']['processingEnvironment']
     * 		$transactionControl['options']['country']
     * 		$transactionControl['options']['language']
     * 		$transactionControl['options']['pointOfSaleIndicator']
     * @param int $mode, 0 => test, 1=> live
     * @throws Exception if no transactionControl Set
     * @return Transunion
     */
    public function __construct($certificate = array(), $transactionControl = array(), $mode = 0) {
        $this->mode = ($mode == 1) ? 1 : 0;
        $this->base_url = ($mode == 1) ? 'https://netaccess.transunion.com/' : 'https://netaccess-test.transunion.com/';

        //TODO, verify all required parameters.
        if (sizeof($certificate))
            $this->certificate = $certificate;
        else
            throw new Exception('Transunion: Missing Certificate Info');

        //TODO, verify all required parameters.
        if (sizeof($transactionControl))
            $this->transactionControl = $transactionControl;
        else
            throw new Exception('Transunion: Missing Transaction Control');
    }

    /**
     * Verify
     * @param Array $name All three names of consumer e.g. array('first'=>'Joe', 'middle'=>'', 'last'=>'Smith')
     * @param String $dob Date of birth in YYYY-MM-DD format
     * @param Array $address e.g array('number','name','city','state','zip')
     * @param String $social Social Security # format 111111111
     * @throws Exception if request fails (see private function request() for details)
     * @return Array
     */
    public function creditReport($name, $dob, $address, $social) {

        $product = array();
        $product['code'] = '07000';
        $subjectRecord = array();
        $subjectRecord['indicative'] = array();

        //NAME
        $subjectRecord['indicative']['name'] = array();
        $subjectRecord['indicative']['name']['person'] = array();
        $subjectRecord['indicative']['name']['person'] = $name;

        //ADDRESS
        $subjectRecord['indicative']['address'] = array();
        $subjectRecord['indicative']['address']['status'] = 'current'; //Primay Home
        $subjectRecord['indicative']['address']['street'] = array('number' => $address['number'], 'name' => $address['name']);
        $subjectRecord['indicative']['address']['location'] = array('city' => $address['city'], 'state' => $address['state'], 'zipCode' => $address['zipCode']);
        $subjectRecord['indicative']['address']['residence'] = array();

        //SOCIAL
        $subjectRecord['indicative']['socialSecurity'] = array('number' => $social);

        //DOB
        $subjectRecord['indicative']['dateOfBirth'] = null;

        //addOnProduct				
        $subjectRecord['addOnProduct'] = array('code' => '00P02', 'scoreModelProduct' => 'true');

        $product['subject'] = array();
        $product['subject']['number'] = 1;


        $product['subject']['subjectRecord'] = $subjectRecord;
        $product['responseInstructions'] = array('returnErrorText' => 'true', 'document' => null);
        $product['permissiblePurpose'] = array('inquiryECOADesignator' => 'individual');

        return $this->request($product);
    }

    public function IDReport($firstName, $lastName, $ssn) {
        $product = [];
        $product['code'] = '07770';
        $product['permissiblePurpose']['code'] = 'EP';
        $product['permissiblePurpose']['endUser'] = 'Accutrace';
        $product['responseInstructions']['returnErrorText'] = 'true';
        $product['subject']['number'] = '1';
        $product['subject']['subjectRecord']['indicative']['name']['person']['first'] = $firstName;
        $product['subject']['subjectRecord']['indicative']['name']['person']['last'] = $lastName;
        $product['subject']['subjectRecord']['indicative']['socialSecurity']['number'] = $ssn;
        // $product['subject']['subjectRecord']['indicative']['dateOfBirth'] = $dob;
        $product['subject']['subjectRecord']['addOnProduct']['code'] = '07220';
        $product['subject']['subjectRecord']['addOnProduct']['scoreModelProduct'] = 'false';
        return $this->request($product);
    }

    /**
     * (Private) Build CURL request
     * @param array $product
     * @throws Exception if request fails (HTTP error, API-level error, or response parsing error)
     * @return Array
     */
    protected function request($product = array(), $document = "request") {

        error_reporting(E_ALL);
        ini_set('display_errors', 1);

        $request = array();
        $request['document'] = $document;
        $request['version'] = Transunion::api_version;
        $request['transactionControl'] = $this->transactionControl;
        $request['product'] = $product;

        //creating object of SimpleXMLElement
        $xml_user_info = new \SimpleXMLElement("<?xml version=\"1.0\"?><creditBureau xmlns=\"http://www.transunion.com/namespace\" xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" xsi:schemaLocation=\"http://www.transunion.com/namespace\"></creditBureau>");

        // //function call to convert array to xml
        $this->array_to_xml($request, $xml_user_info);

        $xml = $xml_user_info->asXML();
        echo 'Request: ' . PHP_EOL;
        echo $xml;
        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_AUTOREFERER, true);

            curl_setopt($ch, CURLOPT_URL, $this->base_url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);

            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
            //curl_setopt($ch, CURLOPT_CAPATH, '/etc/ssl/certs/');
            curl_setopt($ch, CURLOPT_SSLCERT, $this->certificate['crt']);
            curl_setopt($ch, CURLOPT_SSLKEY, $this->certificate['key']);
            curl_setopt($ch, CURLOPT_SSLCERTPASSWD, $this->certificate['password']);
            curl_setopt($ch, CURLOPT_SSLKEYPASSWD, $this->certificate['password']);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 60);

            $response = curl_exec($ch);
            echo "Repsonse: " . PHP_EOL;
            if (FALSE === $response) {
                throw new Exception(curl_error($ch), curl_errno($ch));
            }

            curl_close($ch);
            return simplexml_load_string($response, 'SimpleXMLElement', LIBXML_NOCDATA);
        } catch (Exception $e) {

            trigger_error(sprintf(
                            'Curl failed with error #%d: %s', $e->getCode(), $e->getMessage()), E_USER_ERROR);
        }
    }

    //function defination to convert array to xml
    private function array_to_xml($array, $xml_user_info) {
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                if (!is_numeric($key)) {
                    $subnode = $xml_user_info->addChild("$key");
                    $this->array_to_xml($value, $subnode);
                } else {
                    $subnode = $xml_user_info->addChild("item$key");
                    $this->array_to_xml($value, $subnode);
                }
            } else {
                $xml_user_info->addChild("$key", htmlspecialchars("$value"));
            }
        }
    }

}
