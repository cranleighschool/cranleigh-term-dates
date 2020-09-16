<?php
/**
 * Created by PhpStorm.
 * User: fredbradley
 * Date: 02/11/2017
 * Time: 12:07
 */

namespace CranleighSchool\TermDates;

/**
 * Class TermDetails
 *
 * @package CranleighSchool\TermDates
 */
class TermDetails {

	/**
	 * @var array
	 */
	protected $return = [];
	/**
	 * @var array
	 */
	protected $termMeta;
	/**
	 * @var string
	 */
	protected $termKey;
	/**
	 * @var string
	 */
	protected $metaPrefix;

	/**
	 * TermDetails constructor.
	 *
	 * @param array  $term_meta
	 * @param string $term_key
	 * @param string $meta_prefix
	 */
	public function __construct(array $term_meta, string $term_key, string $meta_prefix ) {

		$this->termMeta   = $term_meta;
		$this->termKey    = $term_key;
		$this->metaPrefix = $meta_prefix;
	}

	/**
	 * @return array
	 */
	public function getCollection() {

		foreach ( $this->termMeta as $each_meta ) {
			$meta           = new TermMeta( $each_meta, $this->termKey, $this->metaPrefix );
			$this->return[] = $meta;
		}

		return $this->return;
	}

}
