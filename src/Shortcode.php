<?php
/**
 * Created by PhpStorm.
 * User: fredbradley
 * Date: 02/11/2017
 * Time: 10:10
 */

namespace CranleighSchool\TermDates;


class Shortcode extends Controller {

	public function __construct() {

		parent::__construct();
		add_shortcode( 'termdates', [ $this, 'shortcode' ] );
	}

	public function shortcode( $atts, $content = null ) {

		$meta = $this->tidy_meta();

		echo $this->displayTable( $meta );
	}

	private function displayTable( array $meta ) {

		ob_start();
		echo '<div class="term-dates-tab-container">';
		echo '<ul class="nav nav-tabs">';
		foreach ( $meta as $yearGroup => $year ) {
			echo "\n";
			echo '<li class="' . $this->checkActiveTab( $yearGroup ) . '">';
			echo '<a href="#' . $yearGroup . '" data-toggle="tab">' . $yearGroup . '&ndash;' . ( $yearGroup + 1 ) . '</a>';
			echo '</li>';
			echo "";
		}
		echo "\n";
		echo '</ul>';

		echo '<div class="tab-content clearfix">';

		foreach ( $meta as $yearGroup => $year ) {
			echo '<div class="tab-pane ' . $this->checkActiveTab( $yearGroup ) . '" id="' . $yearGroup . '">';
			echo "<h2>Academic Year " . $yearGroup . "-" . ( $yearGroup + 1 ) . "</h2>";

			foreach ( $year as $term ) {
				echo "<h3>" . $term->title . "</h3>";
				if ( empty( $term->meta ) ) {
					echo '<p class="text-danger">Apologies, no detail has yet been added.</p>';
				} else {
					?>

					<table class="table table-striped">
						<tbody>
						<?php
						foreach ( $term->meta as $meta ) {
							echo "<tr>";
							echo "<td><strong>" . $meta->title . "</strong></td>";
							echo "<td>" . $meta->value . "</td>";
							echo "</tr>";
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

	private function checkActiveTab( int $yearGroup ) {

		if ( $yearGroup == $this->currentAcademicYear ) {
			$class = "active";
		} else {
			$class = null;
		}

		return $class;
	}
}
