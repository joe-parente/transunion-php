<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

//Non-Composer Auto-load Example
require_once('src/Transunion/Transunion.php');

//https://techservices.transunion.com/ctsportal/techservices/public/docrepository/tuxml/collectionCreditReport_samples.page?

$transactionControl = array();
	$transactionControl['userRefNumber'] = '---';
	$transactionControl['subscriber'] = array();
		$transactionControl['subscriber']['industryCode'] = "---";
		$transactionControl['subscriber']['memberCode'] = "---";
		$transactionControl['subscriber']['inquirySubscriberPrefixCode'] = "---";
		$transactionControl['subscriber']['password'] = "---";
	$transactionControl['options'] = array();
		$transactionControl['options']['processingEnvironment'] = "---";
		$transactionControl['options']['country'] = "---";
		$transactionControl['options']['language'] = "---";
		$transactionControl['options']['contractualRelationship'] = "---";
		$transactionControl['options']['pointOfSaleIndicator'] = "---";

$certificate = array();
	$certificate['key'] = "---";
	$certificate['crt'] = "---";
	$certificate['password'] = "---";

$Transunion = new Transunion\Transunion($certificate,$transactionControl);

//Test DB User
$creditReport = $Transunion->creditReport(
						array('first'=>'PETER','middle'=>'H','last'=>'WONG'),
						null, 
						array('number'=>'85','name'=>'WALNUT','city'=>'NEWARK','state'=>'NJ','zipCode'=>'07102-4716'),
						'666333250'
					          );

echo "<pre>";
print_R($creditReport);