	<?php

	/**
	 * Class Request
	 * Handles HTTP request validation and JSON body parsing
	 */
	class Request
	{
		private string $error_code = '0'; // Current error code
		private array $data = [];         // Parsed request data
		private bool $has_error = false;  // Error state flag

		public function __construct() {}

		/**
		 * Check if any error occurred in the request
		 */
		private function is_error_occured(): bool
		{
			return $this->has_error;
		}

		/**
		 * Get the current error code
		 */
		private function get_error_code() :string
		{
			return $this->error_code;
		}

		/**
		 * Ensure the request method is POST
		 */
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

		/**
		 * Validate the Content-Type header
		 */
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

		/**
		 * Receive and parse JSON request body
		 */
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

		/**
		 * Return the final result with error code and parsed data
		 */
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