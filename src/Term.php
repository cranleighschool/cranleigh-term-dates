<?php
/**
 * Created by PhpStorm.
 * User: fredbradley
 * Date: 02/11/2017
 * Time: 11:04
 */

namespace CranleighSchool\TermDates;

/**
 * Class Term
 *
 * @package CranleighSchool\TermDates
 */
class Term {

	/**
	 * @var int
	 */
	public $year;
	/**
	 * @var string
	 */
	public $title;
	/**
	 * @var array
	 */
	public $meta = [];
	/**
	 * @var string
	 */
	public $term_key;

	/**
	 * Term constructor.
	 *
	 * @param string $term
	 */
	public function __construct(string $term ) {

		$this->term_key    = $term;
		$this->meta_prefix = Admin::getMetaPrefix();
		$this->year        = $this->getSanitizedYear();
		$this->title       = $this->getSanitizedTitle();
		$this->meta        = $this->get_post_meta();
	}

	/**
	 * @return int
	 */
	private function getSanitizedYear() {

		$int = str_replace( '-', '', filter_var( $this->term_key, FILTER_SANITIZE_NUMBER_INT ) );

		return (int) $int;
	}

	/**
	 * @return string
	 */
	private function getSanitizedTitle() {

		$title = str_replace( '-', ' ', $this->term_key );

		return ucwords( $title );
	}

	/**
	 * @return array
	 */
	private function get_post_meta() {

		$term_meta = get_post_meta( get_the_ID(), $this->meta_prefix . $this->term_key, true );
		$details   = new TermDetails( $term_meta, $this->term_key, $this->meta_prefix );

		return $details->getCollection();
	}

}
