<?php

namespace Gothick\GoogleCloudPrint;

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
	const APIBASE = 'https://www.google.com/cloudprint';

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

	/**
	 * Submit a document to a print queue.
	 * 
	 * @param StreamInterface $document
	 * @param string $type
	 * @param GoogleCloudPrinter $printer
	 */
	function submit($document, $content_type, $printer_id, $title = null) {
		if (empty($title)) {
			$title = 'Gothick-GoogleCloudPrint-' . (string) microtime();
		}

		$response = $this->httpClient->request(
			'POST', 
			self::APIBASE . '/submit', [
				'form_params' => [
						'title' => $title,
						'printerid' => $printer_id,
						'contentType' => $content_type,
						'contentTransferEncoding' => 'base64',
						// TODO: When Guzzle has an obvious way of adding a stream filter like
						// base64, use the actual stream rather than converting the whole thing
						// to a string in memory.
						'content' => base64_encode((string) $document)
				]
			]
		);
		// TODO: Error checking
	}

	/**
	 * Search for printers
	 * 
	 * @param unknown $search Search parameters, e.g. "Brother"
	 * @return \Gothick\GoogleCloudPrint\GoogleCloudPrinter[]
	 */
	function search($search = null) {
		// TODO: Allow passing of all the standard parameters. Perhaps 
		// chuck in an array and do an array_merge here of defaults?
		$params = array();
		if (!empty($search)) {
			$params['q'] = $search;
		}
		$response = $this->httpClient->request('GET', self::APIBASE . '/search', [
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
	
	/**
	 * Mostly for my own debugging and other edification, return the JSON
	 * representation of the specicfied printer's capabilities.
	 *
	 * @return \GuzzleHttp\Psr7\Stream
	 * @param string $printer_id
	 * @param array $extra_fields
	 */
	function printer($printer_id, $extra_fields = array()) {
		$response = $this->httpClient->request('GET', self::APIBASE . '/printer', [
				'query' => [
						'printerid' => $printer_id,
						'extra_fields' => implode(",", $extra_fields)
				]
		]);
		return (string) $response->getBody();
	}

	/**
	 * Simple utility function for accepting invitations to a printer. Useful
	 * for the initial sharing of your printer with your Service Account.
	 * 
	 * @param int $printer_id
	 * @return string
	 */
	function acceptInvitation($printer_id) {
		// Thanks for the tip, jr997 and Wolfgang
		// https://stackoverflow.com/a/36366114/300836
		// TODO: Error handling. What do we need? It definitely sends us
		// back some JSON with "success": "false" if it goes wrong; you
		// can get that by not specifying a printer id.
		$response = $this->httpClient->request(
			'POST',
			self::APIBASE . '/processinvite', [
					'form_params' => [
						'printerid' => $printer_id,
						'accept' => 'true'
					]
			]
		);
		return (string) $response->getBody();
	}
}