<?php

namespace Gothick\GoogleCloudPrint;

require_once __DIR__ . '/../vendor/autoload.php';

/**
 * You *must* set GOOGLE_APPLICATION_CREDENTIALS to the path of a service account's key (JSON format)
 * before constructing a GoogleCloudPrint.
 * 
 * @author matt
 *
 */

class GoogleCloudPrint {
	// TODO: When we move to PHP 7.1, we can use access modifiers on 
	// class constants.
	const APIBASE = 'https://www.google.com/cloudprint/';
	
	// TODO: Do we need to keep this hanging around? Or can we just set
	// up the httpClient and use it without needing the Google_Client?
	private $google_client;
	private $httpClient;

	function __construct() {
		// Yes, Google have shoved their stuff in the global namespace. Sigh.
		$this->client = new \Google_Client();
		$this->client->useApplicationDefaultCredentials();
		$this->client->addScope('https://www.googleapis.com/auth/cloudprint');
		$this->httpClient = $this->client->authorize();
	}

	/*function print($document, $type, GoogleCloudPrinter $printer) {
	}
	*/
	// search
	function printers() {
		$response = $this->httpClient->request('GET', self::APIBASE . 'search');
		return (string) $response->getBody();
	}
}