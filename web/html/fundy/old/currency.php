<?php
$time_start = microtime ( true );

ini_set ( 'session.save_path', dirname ( __FILE__ ) . '/session' );
include (dirname ( __FILE__ ) . '/../.ba&4AhAF_mysql.php');
/*
 * Copyright 2013 Google Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
session_start ();
include_once "google-api-php-client/examples/templates/base.php";

/**
 * **********************************************
 * Make an API request authenticated with a service
 * account.
 * **********************************************
 */
require_once realpath ( dirname ( __FILE__ ) . '/google-api-php-client/src/Google/autoload.php' );

/**
 * **********************************************
 * ATTENTION: Fill in these values! You can get
 * them by creating a new Service Account in the
 * API console.
 * Be sure to store the key file
 * somewhere you can get to it - though in real
 * operations you'd want to make sure it wasn't
 * accessible from the webserver!
 * The name is the email address value provided
 * as part of the service account (not your
 * address!)
 * Make sure the Books API is enabled on this
 * account as well, or the call will fail.
 * **********************************************
 */
$client_id = '641681166632-bla6hfsagifsp0m67ej8ndv4cbuf6csd.apps.googleusercontent.com'; // Client ID
$service_account_name = 'fundy-1378@appspot.gserviceaccount.com'; // Email Address
$key_file_location = dirname ( __FILE__ ) . '/Fundy-5f1e8fcca213.p12'; // key.p12
                                                                       // IhWFDrTbjBdbCb0GNwlN0Iyw
                                                                       // echo pageHeader ( "Service Account Access" );
if (strpos ( $client_id, "googleusercontent" ) == false || ! strlen ( $service_account_name ) || ! strlen ( $key_file_location )) {
	echo missingServiceAccountDetailsWarning ();
	exit ();
}

$client = new Google_Client ();
$client->setApplicationName ( "Client_Library_Examples" );
$service = new Google_Service_Sheets ( $client );

/**
 * **********************************************
 * If we have an access token, we can carry on.
 * Otherwise, we'll get one with the help of an
 * assertion credential. In other examples the list
 * of scopes was managed by the Client, but here
 * we have to list them manually. We also supply
 * the service account
 * **********************************************
 */
if (isset ( $_SESSION ['service_token'] )) {
	$client->setAccessToken ( $_SESSION ['service_token'] );
}
$key = file_get_contents ( $key_file_location );
$cred = new Google_Auth_AssertionCredentials ( $service_account_name, array (
		'https://www.googleapis.com/auth/spreadsheets' 
), $key );
$client->setAssertionCredentials ( $cred );
if ($client->getAuth ()->isAccessTokenExpired ()) {
	$client->getAuth ()->refreshTokenWithAssertion ( $cred );
}
$_SESSION ['service_token'] = $client->getAccessToken ();

$array = array ();

// for cmd test
if (! isset ( $_GET ['SID'] )) {
	$SID = '1IeflNPNSikGe7xq4T4mvkiK_jUOxjDEymolXzU8Iqn4';
} else {
	$SID = $_GET ['SID'];
}

$spreadsheetId = $SID;
$range = 'sheet1!A1:ZZ9999';
$response = $service->spreadsheets_values->get ( $spreadsheetId, $range );
$values = $response->getValues ();

if (count ( $values ) == 0) {
	print "No data found.<br>";
} else {
	// echo json_encode ( $values, true );
	
	for($i = 0; $i < count ( $values ); $i ++) {
		$row = $values [$i];
		if ($row [1] == "HKD") {
			mysql_query ( 'USE fundy' );
			mysql_query ( "
				CREATE TABLE IF NOT EXISTS `currency_" . $row [1] . "_" . date ( "Y_m_d" ) . "` (
				  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
				  `create_datetime` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
				  `currency` varchar(45) NOT NULL,
				  `price` varchar(45) NOT NULL,
				  PRIMARY KEY (`id`),
				  UNIQUE KEY `id_UNIQUE` (`id`),
				  KEY `currency` (`currency`)
				) ENGINE=InnoDB DEFAULT CHARSET=utf8;
				" );
			
			for($ii = 2; $ii < count ( $row ); $ii ++) {
				$cmd = "
				INSERT INTO `fundy`.`currency_" . $row [1] . "_" . date ( "Y_m_d" ) . "`
				(`currency`, `price`)
				VALUES
	 		 ";
				
				$iquery = "";
				
				$currency = $values [1] [$ii];
				
				$price = $row [$ii];
				$col = "'" . $row [$ii] . "'";
				
				$iquery = $iquery . "," . $col;
				
				$cmd = $cmd . "('" . $currency . "'," . substr ( $iquery, 1 ) . "),";
				
				if (count ( sql_select_array ( "
					  SELECT ID
					  FROM `fundy`.`currency_" . $row [1] . "_" . date ( "Y_m_d" ) . "`
					  WHERE currency = '" . $currency . "'
					  AND price = '" . $price . "' 
					  AND create_datetime > DATE_ADD(NOW(), INTERVAL -30 SECOND)
					  LIMIT 1
					  " ) ) < 1) {
					
					echo $cmd;
					
					echo '<br>' . sql_insert_id ( substr ( $cmd, 0, - 1 ) );
					
					echo "mysql_errno: ";
					echo mysql_errno ( $_MYSQLCONNECTION ) . mysql_error ( $_MYSQLCONNECTION ) . "\n";
				}
			}
		}
	}
}
echo (microtime ( true ) - $time_start);
?>