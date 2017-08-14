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

use Gothick\GoogleCloudPrint\GoogleCloudPrinter;

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
	function printers($search = null) {
		$params = array();
		if (!empty($search)) {
			$params['q'] = $search;
		}
		$response = $this->httpClient->request('GET', self::APIBASE . 'search', [
				'query' => $params
			]
		);
		$jsonobj = json_decode((string) $response->getBody());

		$printers = array();
		if ($jsonobj->success) {
			if (isset($jsonobj->printers)) {
				foreach ($jsonobj->printers as $printer) {
					$printers[] = new GoogleCloudPrinter($printer);
				}
			}
		} else {
			//TODO: Handle errors somehow.
		}
		return $printers;
	}

	function acceptInvitation($printer_id) {
		// Thanks for the tip, jr997 and Wolfgang
		// https://stackoverflow.com/a/36366114/300836
		// TODO: Error handling. What do we need? It definitely sends us
		// back some JSON with "success": "false" if it goes wrong; you
		// can get that by not specifying a printer id.
		$response = $this->httpClient->request(
			'POST',
			self::APIBASE . 'processinvite', [
					'form_params' => [
						'printerid' => $printer_id,
						'accept' => 'true'
					]
			]
		);
		return (string) $response->getBody();
	}
}