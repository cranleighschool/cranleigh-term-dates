<?php
/**
 * Created by PhpStorm.
 * User: fredbradley
 * Date: 02/11/2017
 * Time: 11:04
 */

namespace CranleighSchool\TermDates;

class Term {

	public $year;
	public $title;
	public $meta = [];
	public $term_key;

	public function __construct( string $term ) {

		$this->term_key    = $term;
		$this->meta_prefix = Admin::getMetaPrefix();
		$this->year        = $this->getSanitizedYear();
		$this->title       = $this->getSanitizedTitle();
		$this->meta        = $this->get_post_meta();
	}

	private function getSanitizedYear() {

		$int = str_replace( "-", "", filter_var( $this->term_key, FILTER_SANITIZE_NUMBER_INT ) );

		return (int) $int;
	}

	private function getSanitizedTitle() {

		$title = str_replace( "-", " ", $this->term_key );

		return ucwords( $title );
	}

	private function get_post_meta() {

		$term_meta = get_post_meta( get_the_ID(), $this->meta_prefix . $this->term_key, true );
		$details   = new TermDetails( $term_meta, $this->term_key, $this->meta_prefix );

		return $details->getCollection();
	}

}
