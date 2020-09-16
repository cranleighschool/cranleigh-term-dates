<?php
	/**
	 * Created by PhpStorm.
	 * User: fredbradley
	 * Date: 02/11/2017
	 * Time: 10:15
	 */

	namespace CranleighSchool\TermDates;

	/**
	 * Class Controller
	 *
	 * @package CranleighSchool\TermDates
	 */
	abstract class Controller
	{

		/**
		 * @var string
		 */
		public static $meta_prefix = 'term_date_';
		/**
		 * @var array
		 */
		public $years = [];
		/**
		 * @var
		 */
		public $currentAcademicYear;


		/**
		 * Controller constructor.
		 */
		public function __construct()
		{

			$this->setCurrentAcademicYear();
			$this->setYears($this->currentAcademicYear);
			$this->sanitizeYears();
		}

		/**
		 * @param int $from
		 */
		public function setYears(int $from): void
		{

			$this->years = range($from, date('Y') + 1);
		}

		/**
		 *
		 */
		public function setCurrentAcademicYear(): void
		{

			$augToDecember = [8, 9, 10, 11, 12];
			if (in_array(date('m'), $augToDecember)) {
				$year = date('Y');
			} else {
				$year = date('Y') - 1;
			}
			$this->currentAcademicYear = $year;
		}

		/**
		 *
		 */
		public function sanitizeYears(): void
		{

			foreach ($this->years as $key => $year) {
				if (time() > mktime(0, 0, 0, 7, 15, $year + 1)) {
					unset($this->years[ $key ]);
				}
			}
		}

		/**
		 * @return array
		 */
		public function tidy_meta(): array
		{

			if (!get_the_ID()) {
				return false;
			}

			$all = get_post_meta(get_the_ID());

			$preg = preg_filter('/^' . self::$meta_prefix . '(.*)/', '$1', array_keys($all));
			$meta = [];

			foreach ($preg as $term) {
				$obj = new Term($term);
				$meta[] = $obj;
			}

			$asYear = array_chunk($meta, 3);

			$years = [];

			foreach ($asYear as $year) {
				if ($year[0]->year < $this->currentAcademicYear) {
					continue;
				}

				if ($this->isActiveYear($year) === false) {
					continue;
				}

				$years[ $year[0]->year ] = $year;

			}

			return $years;

		}

		/**
		 * @param $year
		 *
		 * @return bool
		 */
		private function isActiveYear($year): bool
		{
			if (empty($year[0]->meta) && empty($year[1]->meta) && empty($year[2]->meta)) {
				return false;
			} else {
				return true;
			}
		}

	}
