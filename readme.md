# transunion-php

Basic SDK for Trans Union XML API  [Trans Union API](https://techservices.transunion.com/ctsporta).

## Dependencies

PHP 5.2+ w/ fopen wrapper and SSL extensions enabled and cURL

## Getting Started

### Initializing Transunion

```php
require_once('src/Transunion/Transunion.php');
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
```





## Credit Reports


```php
//Test DB User
$creditReport = $Transunion->creditReport(
						array('first'=>'PETER','middle'=>'H','last'=>'WONG'),
						null, 
						array('number'=>'85','name'=>'WALNUT','city'=>'NEWARK','state'=>'NJ','zipCode'=>'07102-4716'),
						'666333250'
					          );

echo "<pre>";
print_R($creditReport);
```

## Examples

Please see `example.php` for more information.
