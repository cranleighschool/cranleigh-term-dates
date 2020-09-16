<?php


	namespace CranleighSchool\TermDates;


	/**
	 * Class Settings
	 *
	 * @package CranleighSchool\TermDates
	 */
	class Settings
	{
		/**
		 * Settings constructor.
		 */
		public function __construct(string $option)
		{
			foreach (get_option(SettingsPage::getOptionName($option)) as $key => $value) {
				$this->$key = $value;
			}
		}
	}
