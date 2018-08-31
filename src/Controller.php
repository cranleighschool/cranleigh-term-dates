<?php
/**
 * Created by PhpStorm.
 * User: fredbradley
 * Date: 02/11/2017
 * Time: 10:15
 */

namespace CranleighSchool\TermDates;


abstract class Controller {

	public static $meta_prefix = "term_date_";
	public $years = [];
	public $currentAcademicYear;


	public function __construct() {

		$this->setCurrentAcademicYear();
		$this->setYears( $this->currentAcademicYear );
		$this->sanitizeYears();
	}

	public function setYears( int $from ) {

		$this->years = range( $from, date( "Y" ) + 1 );
	}

	public function setCurrentAcademicYear() {

		$augToDecember = [ 8, 9, 10, 11, 12 ];
		if ( in_array( date( 'm' ), $augToDecember ) ) {
			$year = date( 'Y' );
		} else {
			$year = date( 'Y' ) - 1;
		}
		$this->currentAcademicYear = $year;
	}

	public function sanitizeYears() {

		foreach ( $this->years as $key => $year ) {
			if ( time() > mktime( 0, 0, 0, 7, 15, $year + 1 ) ) {
				unset( $this->years[ $key ] );
			}
		}
	}

	public function tidy_meta() {

		if (!get_the_ID()) {
			return false;
		}

		$all  = get_post_meta( get_the_ID() );

		$preg = preg_filter( '/^' . self::$meta_prefix . '(.*)/', '$1', array_keys( $all ) );
		$meta = [];

		foreach ( $preg as $term ) {
			$obj = new Term( $term );
			$meta[] = $obj;
		}

		$asYear = array_chunk( $meta, 3 );

		$years = [];

		foreach ( $asYear as $year ) {
			if ($year[ 0 ]->year < $this->currentAcademicYear) {
				continue;
			}

			if ($this->isActiveYear($year)===false) {
				continue;
			}

			$years[ $year[ 0 ]->year ] = $year;

		}

		return $years;

	}

	private function isActiveYear($year) {
		if (empty($year[0]->meta) && empty($year[1]->meta) && empty($year[2]->meta)) {
			return false;
		} else {
			return true;
		}
	}

}
