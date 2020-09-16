<?php

	namespace CranleighSchool\TermDates;
	/**
	 * WordPress settings API demo class
	 *
	 * @author Tareq Hasan
	 */
	class SettingsPage
	{
		private $pageTitle = "Term Dates Settings";

		/**
		 * @var \CranleighSchool\TermDates\TareqSettingsClass
		 */
		private $settings_api;

		/**
		 * SettingsPage constructor.
		 */
		function __construct()
		{
			$this->settings_api = new TareqSettingsClass();

			add_action('admin_init', array($this, 'admin_init'));
			add_action('admin_menu', array($this, 'admin_menu'));
		}

		/**
		 *
		 */
		function admin_init()
		{

			//set the settings
			$this->settings_api->set_sections($this->get_settings_sections());
			$this->settings_api->set_fields($this->get_settings_fields());

			//initialize settings
			$this->settings_api->admin_init();
		}

		/**
		 * @return array
		 */
		function get_settings_sections()
		{
			$sections = array(
				array(
					'id'    => self::getOptionName('isamsAPI'),
					'title' => __('ISAMS API', 'cranleigh-2016')
				),
				array(
					'id'    => self::getOptionName('socsAPI'),
					'title' => __('SOCS API', 'cranleigh-2016')
				)
			);

			return $sections;
		}

		public static function getOptionName(string $name)
		{
			return Controller::$meta_prefix . $name;
		}

		/**
		 * Returns all the settings fields
		 *
		 * @return array settings fields
		 */
		function get_settings_fields(): array
		{
			$settings_fields = array(
				self::getOptionName('isamsAPI') => [
					[
						'name'  => 'domain',
						'label' => 'Domain'
					],
					[
						'name'  => 'client_id',
						'label' => 'Client ID',
					],
					[
						'name'  => 'client_secret',
						'label' => 'Client Secret'
					]

				],
				self::getOptionName('socsAPI')  => [
					[
						'name'  => 'schoolId',
						'label' => 'School ID'
					],
					[
						'name'  => 'apiKey',
						'label' => 'API Key'
					]
				]
			);

			return $settings_fields;
		}

		/**
		 *
		 */
		function admin_menu()
		{
			add_options_page($this->pageTitle, $this->pageTitle, 'delete_posts', 'term-dates-settings', array($this, 'plugin_page'));
		}

		/**
		 *
		 */
		function plugin_page()
		{
			echo '<div class="wrap">';
			echo '<h1>' . $this->pageTitle . '</h1>';

			$this->settings_api->show_navigation();
			$this->settings_api->show_forms();

			echo '</div>';
		}

	}
