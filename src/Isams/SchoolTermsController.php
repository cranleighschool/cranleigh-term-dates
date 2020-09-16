<?php


	namespace CranleighSchool\TermDates\Isams;

	use Carbon\Carbon;
	use CranleighSchool\TermDates\FredTerm;
	use GuzzleHttp\Exception\ClientException;
	use Tightenco\Collect\Support\Collection;


	class SchoolTermsController extends Endpoint
	{
		private $terms = NULL;

		/**
		 * @return object
		 * @throws \GuzzleHttp\Exception\GuzzleException
		 */
		public function getCurrentTerm(): object
		{
			$terms = $this->index();

			$findTerm = $terms->filter(function ($item) {
				if (Carbon::now()->between(Carbon::parse($item->startDate), Carbon::parse($item->finishDate))) {
					return $item;
				}
			});

			if ($findTerm->count() === 1) {
				return $findTerm->first();
			} else {
				return $this->getNearestTerm();
			}
		}

		/**
		 * @return Collection
		 * @throws \GuzzleHttp\Exception\GuzzleException
		 */
		public function index(): Collection
		{
			if ($this->terms !== NULL) {
				return $this->terms;
			}
			try {
				$response = $this->guzzle->request('GET', $this->endpoint, ['headers' => $this->getHeaders()]);
				$this->terms = collect(json_decode($response->getBody()->getContents())->terms);
				return $this->terms;
			} catch (ClientException $exception) {
				if ($exception->getCode() == 401) {
					return $this->reAuthenticate(self::class, __FUNCTION__);
				}
				throw $exception;
			}
		}

		/**
		 * @return object
		 * @throws \GuzzleHttp\Exception\GuzzleException
		 */
		private function getNearestTerm(): object
		{
			$terms = $this->index();
			$nearestTerm = $terms->filter(function ($item) {
				if (Carbon::parse($item->finishDate) > now()->subMonth() && Carbon::parse($item->startDate) < now()->addMonth()) {
					return $item;
				}
			});
			if ($nearestTerm->count() === 1) {
				return $nearestTerm->first();
			} else {
				throw new \Exception('Could not find nearest term', 500);
			}
		}

		/**
		 * @param int $year
		 *
		 * @return Collection
		 */
		public function getYear(int $year): Collection
		{
			$terms = $this->index();

			return $terms->filter(function ($item) use ($year) {
				if ($item->schoolYear === $year) {
					return $item;
				}
			});
		}

		/**
		 * Set the URL the request is made to.
		 *
		 * @return void
		 * @throws \Exception
		 */
		protected function setEndpoint()
		{
			$this->endpoint = $this->getDomain() . '/api/school/terms';
		}
	}
