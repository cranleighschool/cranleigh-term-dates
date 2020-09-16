<?php

	namespace CranleighSchool\TermDates;

	use Carbon\Carbon;
	use CranleighSchool\TermDates\SOCS\CalendarApi;
	use Tightenco\Collect\Support\Collection;

	/**
	 * Class Api
	 *
	 * @package CranleighSchool\TermDates
	 */
	class Api extends \WP_REST_Controller
	{
		/**
		 *
		 */
		public function register_routes()
		{
			register_rest_route(
				'cranleigh/termdates',
				"termdates",
				[
					'methods'  => \WP_REST_Server::READABLE,
					'callback' => [self::class, 'getTermDates']
				]
			);
		}

		/**
		 * @return \WP_REST_Response
		 * @throws \Exception
		 */
		public static function getTermDates()
		{
			if ((get_transient('termDates') instanceof Collection) === false) {
				$socs = new CalendarApi();
				$calendar = $socs->getCalendar(Carbon::now()->startOfDay()->subYear(), Carbon::now()->endOfDay()->addYears(2));
				$calendar = $socs->filterByTermDates($calendar);
				set_transient('termDates', $calendar, MONTH_IN_SECONDS);
			}

			$output = get_transient('termDates');

			return new \WP_REST_Response($output, 200);

		}
	}
