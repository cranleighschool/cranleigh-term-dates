<?php

	namespace CranleighSchool\TermDates\Isams;

	use CranleighSchool\TermDates\Settings;
	use GuzzleHttp\Client as Guzzle;

	class Authenticate
	{
		/**
		 * @var string
		 */
		private $clientId;

		/**
		 * @var string
		 */
		private $authenticationUrl;

		/**
		 * @var string
		 */
		private $clientSecret;

		/**
		 * @var string
		 */
		public $cacheKey;

		public function __construct(Settings $settings)
		{
			$this->getConfig($settings);
		}

		/**
		 * Set the client settings.
		 *
		 * @param Settings $settings
		 *
		 * @return void
		 */
		private function getConfig(Settings $settings)
		{
			$this->clientId = $settings->client_id;
			$this->authenticationUrl = $settings->domain . '/main/sso/idp/connect/token';
			$this->clientSecret = $settings->client_secret;
			$this->cacheKey = 'ISAMS_RestApiAccessToken';
		}

		/**
		 * Get an authentication token from the cache or request a new one.
		 *
		 * @return string
		 * @throws \GuzzleHttp\Exception\GuzzleException
		 */
		public function getToken()
		{
			if (get_transient($this->cacheKey)):
				return get_transient($this->cacheKey);
			endif;

			return $this->requestNewToken();
		}

		/**
		 * Request a new authentication token.
		 *
		 * @return string
		 * @throws \GuzzleHttp\Exception\GuzzleException
		 */
		private function requestNewToken()
		{
			$guzzle = new Guzzle();

			$response = $guzzle->request('POST', $this->authenticationUrl, [
				'headers'     => [
					'cache-control' => 'no-cache',
					'Content-type'  => 'application/x-www-form-urlencoded',
				],
				'form_params' => [
					'grant_type'    => 'client_credentials',
					'client_id'     => $this->clientId,
					'client_secret' => $this->clientSecret,
					'scope'         => 'api',
				],
			]);

			if ($response->getStatusCode() !== 200) {
				throw new \Exception('Unable to request new authentication token, invalid response (Error 500)');
			}

			$data = json_decode($response->getBody()->getContents());

			return $this->cache($data->access_token, $data->expires_in);
		}

		/**
		 * Save the access token to the cache & return it for use.
		 *
		 * @param string $accessToken
		 * @param int    $expiry
		 *
		 * @return string
		 */
		private function cache(string $accessToken, int $expiry)
		{
			$seconds = $expiry;
			set_transient($this->cacheKey, $accessToken, $seconds);

			return get_transient($this->cacheKey);
		}
	}
