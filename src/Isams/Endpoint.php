<?php

	namespace CranleighSchool\TermDates\Isams;

	use CranleighSchool\TermDates\Settings;

	abstract class Endpoint
	{
		/**
		 * @var Guzzle
		 */
		protected $guzzle;

		/**
		 * @var array
		 */
		protected $settings;

		/**
		 * @var string
		 */
		protected $endpoint;

		/**
		 * Endpoint constructor.
		 *
		 * @param \CranleighSchool\TermDates\Settings $settings
		 */
		public function __construct(Settings $settings)
		{
			$this->settings = $settings;
			$this->setGuzzle();
			$this->setEndpoint();
		}

		/**
		 * Instantiate Guzzle
		 *
		 * @return void
		 */
		protected function setGuzzle()
		{
			$this->guzzle = new \GuzzleHttp\Client();
		}

		/**
		 * Set the URL the request is made to
		 *
		 * @return void
		 */
		abstract protected function setEndpoint();

		/**
		 * Get a specific page from the api
		 *
		 * @param string $url
		 * @param int    $page
		 *
		 * @return mixed
		 * @throws \GuzzleHttp\Exception\GuzzleException
		 */
		public function pageRequest(string $url, int $page)
		{
			$response = $this->guzzle->request('GET', $url, [
				'query'   => ['page' => $page],
				'headers' => $this->getHeaders(),
			]);

			return $response->getBody()->getContents();
		}

		/**
		 * Get the Guzzle headers for a request
		 *
		 * @return array
		 */
		protected function getHeaders()
		{
			return [
				'Authorization' => 'Bearer ' . $this->getAccessToken(),
				'Accept'        => 'application/json',
				'Content-Type'  => 'application/json',
			];
		}

		/**
		 * Get an access token for the specified Institution
		 *
		 * @return string
		 */
		protected function getAccessToken()
		{
			return (new Authenticate($this->getSettings()))->getToken();
		}

		/**
		 * Get the School to be queried
		 *
		 * @return \spkm\Isams\Contracts\Institution
		 */
		protected function getSettings()
		{
			return $this->settings;
		}

		/**
		 * Wrap the json returned by the API
		 *
		 * @param        $json
		 * @param string $property
		 * @param string $wrapper
		 *
		 * @return \Illuminate\Support\Collection
		 */
		public function wrapJson($json, string $property, string $wrapper)
		{
			$decoded = json_decode($json);

			return collect($decoded->$property)->map(function ($item) use ($wrapper) {
				return new $wrapper($item);
			});
		}

		/**
		 * Get the domain of the specified Institution
		 *
		 * @return string
		 * @throws \Exception
		 */
		protected function getDomain()
		{
			if (array_key_exists('domain', $this->settings)) {
				return $this->settings->domain;
			}
			throw new \Exception("Domain not set");
		}

		/**
		 * Validate the attributes
		 *
		 * @param array $requiredAttributes
		 * @param array $attributes
		 *
		 * @return bool
		 * @throws \Exception
		 */
		protected function validate(array $requiredAttributes, array $attributes)
		{
			foreach ($requiredAttributes as $requiredAttribute):
				if (array_key_exists($requiredAttribute, $attributes) === false) {
					throw new \Exception("'$requiredAttribute' is required by this endpoint.");
				}
			endforeach;

			return true;
		}

		/**
		 * @param string $class
		 * @param string $function
		 *
		 * Sorry, a little bit hacky, but needed a way if we got a 401,
		 * to reauthenticate deleting the cache and setting a new token
		 *
		 * @return mixed
		 */
		protected function reAuthenticate(string $class, string $function)
		{
			$auth = new Authenticate(new Settings());
			delete_transient($auth->cacheKey);

			return (new $class(new Settings()))->$function;

		}

		/**
		 * Generate the response
		 *
		 * @param int   $expectedStatusCode
		 * @param mixed $response
		 * @param mixed $data
		 * @param array $errors
		 *
		 * @return \Illuminate\Http\JsonResponse
		 */
		protected function response(int $expectedStatusCode, $response, $data, array $errors = [])
		{
			$status = $response->getStatusCode() === $expectedStatusCode ? 'success' : 'error';
			$errors = empty($errors) === true ? NULL : $errors;

			$json = [
				'data'   => $data,
				'status' => $status,
				'code'   => $response->getStatusCode(),
				'errors' => $errors,
			];

			if (isset($response->getHeaders()['Location'])) {
				$location = $response->getHeaders()['Location'][0];
				$id = ltrim(str_replace($this->endpoint, '', $location), '\//');

				$json['location'] = $location;
				if (!empty($id)) {
					$json['id'] = $id;
				}
			}

			return json_encode($json);
//			return response()->json($json, $response->getStatusCode());
		}
	}
