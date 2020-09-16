<?php
	/**
	 * Created by PhpStorm.
	 * User: fredbradley
	 * Date: 02/11/2017
	 * Time: 10:10
	 */

	namespace CranleighSchool\TermDates;

	use Carbon\Carbon;
	use Tightenco\Collect\Support\Collection;

	/**
	 * Class Shortcode
	 *
	 * @package CranleighSchool\TermDates
	 */
	class Shortcode extends Controller
	{

		/**
		 * Shortcode constructor.
		 */
		public function __construct()
		{

			parent::__construct();
			add_shortcode('termdates', [$this, 'shortcode']);
		}

		/**
		 * @param      $atts
		 * @param null $content
		 */
		public function shortcode($atts, $content = NULL)
		{

//			$meta = $this->tidy_meta(); // @deprecated - was used when we were doing this manually
			$termDates = Api::getTermDates();
			$years = new Collection();
			foreach ($termDates->data as $year => $terms) {
				$years->push(new SchoolYear($year, $terms));
			}
//			echo $this->displayTable($meta) // @deprecated - see above
			echo $this->displayTermDates($years);
		}

		/**
		 * @param \Tightenco\Collect\Support\Collection $years
		 *
		 * @return false|string
		 */
		private function displayTermDates(Collection $years)
		{

			ob_start();
			echo '<div class="term-dates-tab-container">';
			echo '<ul class="nav nav-tabs">';
			foreach ($years as $year) {
				echo "\n";
				echo '<li class="' . $this->checkActiveTab($year->year) . '">';
				echo '<a href="#' . $year->year . '" data-toggle="tab">' . $year->year . '&ndash;' . ($year->year + 1) . '</a>';
				echo '</li>';
				echo '';
			}
			echo "\n";
			echo '</ul>';

			echo '<div class="tab-content clearfix">';

			foreach ($years as $year) {
				echo '<div class="tab-pane ' . $this->checkActiveTab($year->year) . '" id="' . $year->year . '">';
				echo '<h2>' . $year->title . '</h2>';

				foreach ($year->terms as $termName => $term) {
					echo '<h3>' . $termName . ' Term</h3>';
					if (empty($term)) {
						echo '<p class="text-danger">Apologies, no detail has yet been added.</p>';
					} else {
						?>

						<table class="table table-striped">
							<tbody>
							<?php
								foreach ($term as $termDate) {
									echo '<tr>';
									echo '<td><strong>' . $termDate->title . '</strong></td>';
									echo '<td>' . $this->formatDateValue($termDate) . '</td>';
									echo '</tr>';
								}
							?>
							</tbody>
						</table>

						<?php
					}
				}
				echo '</div>';
			}
			echo '</div>';
			echo '</div>'; // End Tab Container

			$content = ob_get_contents();
			ob_end_clean();

			return $content;
		}

		/**
		 * @param int $yearGroup
		 *
		 * @return string|null
		 */
		private function checkActiveTab(int $yearGroup)
		{

			if ($yearGroup == $this->currentAcademicYear) {
				$class = 'active';
			} else {
				$class = NULL;
			}

			return $class;
		}

		/**
		 * @param \CranleighSchool\TermDates\TermDate $termDate
		 *
		 * @return string
		 */
		private function formatDateValue(TermDate $termDate): string
		{
			if ($termDate->startTime == $termDate->endTime) {
				$date = $this->formatCarbonDates($termDate->startTime);
			} elseif ($termDate->startTime->format('Y-m-d') == $termDate->endTime->format('Y-m-d')) {
				$date = sprintf("%s", $this->formatCarbonDates($termDate->startTime));
			} else {
				$date = sprintf("%s - %s", $this->formatCarbonDates($termDate->startTime), $this->formatCarbonDates($termDate->endTime));
			}

			/**
			 * Add the Description on...
			 */
			if ($termDate->description) {
				$date = $date . " <em>(" . $termDate->description . ")</em>";
			}

			return $date;

		}

		/**
		 * @param \Carbon\Carbon $date
		 *
		 * @return string
		 */
		private function formatCarbonDates(Carbon $date): string
		{
			$dateFormatWithTime = "l jS F H:i";
			$dateFormatWithoutTime = "l jS F";

			if ($date->format("H:i") == "00:00") {
				return $date->format($dateFormatWithoutTime);
			} else {
				return $date->format($dateFormatWithTime);
			}
		}

		/**
		 * @param array $meta
		 *
		 * @return false|string
		 * @deprecated Was used when we were doing things manually
		 */
		private function displayTable(array $meta)
		{

			ob_start();
			echo '<div class="term-dates-tab-container">';
			echo '<ul class="nav nav-tabs">';
			foreach ($meta as $yearGroup => $year) {
				echo "\n";
				echo '<li class="' . $this->checkActiveTab($yearGroup) . '">';
				echo '<a href="#' . $yearGroup . '" data-toggle="tab">' . $yearGroup . '&ndash;' . ($yearGroup + 1) . '</a>';
				echo '</li>';
				echo '';
			}
			echo "\n";
			echo '</ul>';

			echo '<div class="tab-content clearfix">';

			foreach ($meta as $yearGroup => $year) {
				echo '<div class="tab-pane ' . $this->checkActiveTab($yearGroup) . '" id="' . $yearGroup . '">';
				echo '<h2>Academic Year ' . $yearGroup . '-' . ($yearGroup + 1) . '</h2>';

				foreach ($year as $term) {
					echo '<h3>' . $term->title . '</h3>';
					if (empty($term->meta)) {
						echo '<p class="text-danger">Apologies, no detail has yet been added.</p>';
					} else {
						?>

						<table class="table table-striped">
							<tbody>
							<?php
								foreach ($term->meta as $meta) {
									echo '<tr>';
									echo '<td><strong>' . $meta->title . '</strong></td>';
									echo '<td>' . $meta->value . '</td>';
									echo '</tr>';
								}
							?>
							</tbody>
						</table>

						<?php
					}
				}
				echo '</div>';
			}
			echo '</div>';
			echo '</div>'; // End Tab Container

			$content = ob_get_contents();
			ob_end_clean();

			return $content;
		}
	}
