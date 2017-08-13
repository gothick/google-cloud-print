<?php

namespace Gothick\GoogleCloudPrint;

require_once __DIR__ . '/../vendor/autoload.php';

class GoogleCloudPrinter {
	private $id;
	private $display_name;
	private $details;

	// TODO: Maybe just throw JSON at this and have it rehydrate?
	function __construct($id, $display_name, $details) {
		$this->id = $id;
		$this->display_name = $display_name;
		$this->details = $details;
	}
	public function displayName() {
		return $this->display_name;
	}
	public function id() {
		return $this->id;
	}
}
