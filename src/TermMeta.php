<?php
/**
 * Created by PhpStorm.
 * User: fredbradley
 * Date: 02/11/2017
 * Time: 11:28
 */

namespace CranleighSchool\TermDates;


class TermMeta {

	public $title;
	public $value;

	public function __construct( array $each, string $term_key, string $meta_prefix ) {

		$this->title = $each[ $meta_prefix . $term_key . "_title" ];
		$this->value = $each[ $meta_prefix . $term_key . "_value" ];

	}
}
