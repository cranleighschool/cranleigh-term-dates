<?php

	namespace CranleighSchool\TermDates\SOCS;

	use Carbon\Carbon;
	use CranleighSchool\TermDates\SchoolTerm;
	use CranleighSchool\TermDates\TermDate;
	use CranleighSchool\TermDates\Controller;
	use CranleighSchool\TermDates\Settings;
	use Tightenco\Collect\Support\Collection;

	class CalendarApi
	{
		/**
		 * @var int
		 */
		private $schoolId;
		/**
		 * @var string
		 */
		private $apiKey;

		/**
		 * @var array
		 */
		protected $options = [];

		/**
		 * @var string
		 */
		private $apiUrl = '';


		private $socsEndpoint = 'https://www.socscms.com/socs/xml/SOCScalendar.ashx?';

		/**
		 * SOCSCalendarApi constructor.
		 *
		 * @param School $school
		 */
		public function __construct()
		{
			$settings = new Settings('socsAPI');

			$this->schoolId = $settings->schoolId;
			$this->apiKey = $settings->apiKey;
		}

		/**
		 * Get's the calendar requested as a collection. You can override any of the options by using the $options param.
		 * Specifically, by setting a 'category' key you can choose which category you want to filter by'.
		 *
		 * @param Carbon $startDate
		 * @param Carbon $endDate
		 * @param array  $options
		 *
		 * @return Collection
		 */
		public function getCalendar(Carbon $startDate, Carbon $endDate, array $options = []): Collection
		{
			$this->setOptions($startDate, $endDate, $options);

			$cacheKey = self::getCacheKey($startDate, $endDate);

			try {
				if (!get_transient($cacheKey)) {
					$contents = file_get_contents($this->apiUrl);
					$xml = simplexml_load_string($contents);

					$events = [];
					foreach ($xml->CalendarEvent as $event) {
						$events[] = $event;
					}

					$calendar = collect($events)->mapInto(Event::class);
					set_transient($cacheKey, $calendar, DAY_IN_SECONDS);
				}

				return get_transient($cacheKey);

			} catch (\Exception $exception) {
				throw $exception;
			}
		}

		public function filterByTermDates(Collection $calendar): Collection
		{
			try {
				$filtered = $calendar->filter(function ($value, $key) {
					return $value->Category == $this->options['calendar'] && $value->title !== 'Callover';
				})->mapInto(TermDate::class)->sortBy('startTime')->values();

				return $filtered->groupBy(['schoolYear', 'schoolTerm']);

			} catch (\ErrorException $exception) {


				return new Collection();
			}
		}


		/**
		 * You can override any of the options by passing them through the options param.
		 *
		 * @param Carbon $startDate
		 * @param Carbon $endDate
		 * @param array  $options
		 */
		private function setOptions(Carbon $startDate, Carbon $endDate, array $options = []): void
		{
			$this->options = array_merge(
				[
					'Sport'        => 0,
					'CoCurricular' => 0,
					'startdate'    => $startDate->format('j M Y'),
					'enddate'      => $endDate->format('j M Y'),
					'ID'           => $this->schoolId,
					'key'          => $this->apiKey,
					'calendar'     => 'Term Dates',
				],
				$options
			);

			$this->apiUrl = $this->setUrlOptions($this->options);
		}

		/**
		 * @param array $options
		 *
		 * @return string
		 */
		private function setUrlOptions(array $options = []): string
		{
			$options = http_build_query($this->options);

			return $this->socsEndpoint . $options;
		}

		/**
		 * @param Carbon $startDate
		 * @param Carbon $endDate
		 *
		 * @return string
		 */
		public static function getCacheKey(Carbon $startDate, Carbon $endDate): string
		{
			$str = Controller::$meta_prefix . 'socs_school_calendar';

			return sprintf(
				'%s_%s_%s',
				$str,
				$startDate->format('Y-m-d'),
				$endDate->format('Y-m-d')
			);
		}
	}
