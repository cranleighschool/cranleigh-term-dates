<?php


	namespace CranleighSchool\TermDates\SOCS;


	class Event
	{
		public $title = 'title';
		public function __construct(\SimpleXMLElement $event)
		{
			foreach (get_object_vars($event) as $key => $value) {
				$this->$key = (string) $value;
			}

		}
	}
