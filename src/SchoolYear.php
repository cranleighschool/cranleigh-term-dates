<?php


	namespace CranleighSchool\TermDates;


	use Tightenco\Collect\Support\Collection;

	/**
	 * Class SchoolYear
	 *
	 * @package CranleighSchool\TermDates
	 */
	class SchoolYear
	{
		/**
		 * @var int
		 */
		public $year;
		/**
		 * @var string
		 */
		public $title;
		/**
		 * @var \Tightenco\Collect\Support\Collection
		 */
		public $terms;

		/**
		 * SchoolYear constructor.
		 *
		 * @param int                                   $schoolYear
		 * @param \Tightenco\Collect\Support\Collection $terms
		 */
		public function __construct(int $schoolYear, Collection $terms)
		{
			$this->year = $schoolYear;
			$this->title = $this->setTitle();
			$this->terms = $terms;
		}

		/**
		 * @return string
		 */
		private function setTitle()
		{
			return sprintf("Academic Year %d-%d", $this->year, $this->year + 1);
		}
	}
