<?php


	namespace CranleighSchool\TermDates;

	use Carbon\Carbon;
	use CranleighSchool\TermDates\Isams\SchoolTermsController;
	use CranleighSchool\TermDates\SOCS\Event;
	use Tightenco\Collect\Support\Collection;

	/**
	 * Class SOCSCalendarEvent.
	 */
	class TermDate
	{
		/**
		 * @var string
		 */
		public $title;
		/**
		 * @var string|null
		 */
		public $description;
		/**
		 * @var Carbon
		 */
		public $startTime;
		/**
		 * @var Carbon
		 */
		public $endTime;
		/**
		 * @var string
		 */
		public $category;
		/**
		 * @var int
		 */
		public $eventId;
		/**
		 * @var int
		 */
		public $moduleEventId;

		/**
		 * @var mixed
		 */
		public $schoolTerm;
		/**
		 * @var mixed
		 */
		public $schoolYear;

		/**
		 * SOCSCalendarEvent constructor.
		 *
		 * @param Event $event
		 */
		public function __construct(Event $event)
		{
			$this->eventId = (int)$event->EventID;
			$this->moduleEventId = (int)$event->ModuleEventID;
			$this->startTime = $this->parseDateToCarbon($event->StartDate, $event->StartTime);
			$this->endTime = $this->parseDateToCarbon($event->EndDate, $event->EndTime);
			$this->category = $event->Category;
			$this->title = $event->Title;
			$this->description = (strlen($event->Description)) ? $event->Description : NULL;
			$isamsData = $this->calculateSchoolTerm();
			$this->schoolTerm = $isamsData['schoolTerm'];
			$this->schoolYear = $isamsData['schoolYear'];
		}

		/**
		 * Take the date (and time) that SOCS api gives us and turn into a Carbon instance.
		 *
		 * @param string $date
		 * @param string $time
		 *
		 * @return Carbon
		 */
		private function parseDateToCarbon(string $date, string $time)
		{
			$explodeDate = explode('/', $date);
			if ($time == 'All Day') {
				$time = '00:00';
			}

			return Carbon::parse(sprintf('%s-%s-%s %s', $explodeDate[2], $explodeDate[1], $explodeDate[0], $time));
		}

		/**
		 * @param School $school
		 *
		 * @return array|Collection|null
		 * @throws \GuzzleHttp\Exception\GuzzleException
		 */
		private function calculateSchoolTerm()
		{
			if (strpos($this->title, 'Term') !== false) {
				if ($this->title == 'Start of Term') {
					foreach (self::isamsTermDates() as $term) {
						if ($this->startTime->isSameDay(Carbon::parse($term->startDate))) {
							return $this->termReturnStatement($term->name, $term->schoolYear);
						}
					}
				}
				if ($this->title == 'End of Term') {
					foreach (self::isamsTermDates() as $term) {
						if ($this->endTime->isSameDay(Carbon::parse($term->finishDate))) {
							return $this->termReturnStatement($term->name, $term->schoolYear);
						}
					}
				}

				return;
			}

			foreach (self::isamsTermDates() as $term) {
				if ($this->startTime > $term->startDate && $this->startTime < $term->finishDate) {
					return $this->termReturnStatement($term->name, $term->schoolYear);
				}
			}

			return self::isamsTermDates();
		}

		/**
		 * @return Collection
		 * @throws \GuzzleHttp\Exception\GuzzleException
		 */
		public static function isamsTermDates(): Collection
		{
			$cacheName = "school_term_dates";

			$terms = new SchoolTermsController(new Settings('isamsAPI'));

			return $terms->index()->filter(function ($value, $key) {
				$currentYear = date('Y');

				return in_array(
					$value->schoolYear,
					[
						$currentYear - 1, // Last Year
						$currentYear, // This Year
						$currentYear + 1, // Next Year
					]
				);
			})->values();

		}

		/**
		 * @param string $termName
		 * @param int    $schoolYear
		 *
		 * @return array
		 */
		private function termReturnStatement(string $termName, int $schoolYear): array
		{
			return [
				'schoolTerm' => $termName,
				'schoolYear' => $schoolYear,
			];
		}
	}

