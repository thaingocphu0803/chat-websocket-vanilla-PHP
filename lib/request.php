<?php

class Request
{
	private $error_code = 0;
	private $data = [];
	private $has_error = false;

	public function __construct() {}

	private function is_error_occured(): bool
	{
		return $this->has_error;
	}

	private function get_error_code() :string
	{
		return $this->error_code;
	}

	public function is_post() :Request
	{
		if ($this->is_error_occured()) {
			return $this;
		}

		if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
			$this->has_error = true;
			$this->error_code = 'r-0001';
		}

		return $this;
	}

	public function is_contentType(string $expected) :Request
	{
		if ($this->is_error_occured()) {
			return $this;
		}

		$headers = getallheaders();
		if (isset($headers['Content-Type']) && $headers['Content-Type'] !== $expected) {
			$this->error_code = 'r-0002';
			$this->has_error = true;
		}

		return $this;
	}

	public function receive_json() :Request
	{
		if ($this->is_error_occured()) {
			return $this;
		}

		$json = file_get_contents('php://input');
		$result = json_decode($json, true);

		if (empty($result)) {
			$this->error_code = 'r-0003';
			$this->has_error = true;
			return $this;
		}

		$this->data = $result;

		return $this;
	}


	public function accept(): array
	{
		if ($this->is_error_occured()) {
			return [
				'error_code' => $this->get_error_code(),
				'result' => null,
			];
		}

		return [
			'error_code' => $this->get_error_code(),
			'result' => $this->data,
		];
	}
}

global $request;

$request = new Request();