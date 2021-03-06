<?php

namespace Gothick\GoogleCloudPrint;

class GoogleCloudPrinter {
	private $printer;

	/**
	 *
	 * @param stdClass $printer PHP object hydrated from JSON returned by GoogleCloudPrint's /search.
	 */
	function __construct($printer) {
		$this->printer = $printer;
	}
	public function __get($name) {
		return $this->printer->$name;
	}
	public function __isset($name) {
		return isset($this->printer->$name);
	}
}
