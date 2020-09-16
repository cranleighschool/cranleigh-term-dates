<?php
	/**
	 * Created by PhpStorm.
	 * User: fredbradley
	 * Date: 02/11/2017
	 * Time: 10:11
	 */

	namespace CranleighSchool\TermDates;

	/**
	 * Class Admin
	 *
	 * @package CranleighSchool\TermDates
	 */
	class Admin extends Controller
	{

		/**
		 * Admin constructor.
		 */
		public function __construct()
		{

			parent::__construct();
			add_action('edit_form_after_title', [$this, 'move_metabox']);
			add_filter('rwmb_meta_boxes', [$this, 'metabox']);
			new SettingsPage();
			add_action('rest_api_init', function () {
				$api = new Api();
				$api->register_routes();
			});
		}


		/**
		 * @return string
		 */
		public static function getMetaPrefix(): string
		{

			return self::$meta_prefix;
		}

		/**
		 *
		 */
		public function move_metabox()
		{

			global $post, $wp_meta_boxes;
			do_meta_boxes(get_current_screen(), 'term-dates-meta', $post);
			unset($wp_meta_boxes[ get_post_type($post) ]['term-dates-meta']);
		}

		/**
		 * @param array $meta_boxes
		 *
		 * @return array
		 */
		function metabox(array $meta_boxes): array
		{

			if (!$this->rw_check_termdates_include()) {
				return $meta_boxes;
			}

			$meta_boxes[] = [
				'title'      => 'Term Dates',
				'id'         => 'term-dates',
				'priority'   => 'high',
				'context'    => 'term-dates-meta',
				'post_types' => 'page',
				'autosave'   => true,
				'fields'     => $this->setupTerms(),
			];

			return $meta_boxes;
		}

		/**
		 * @return bool
		 */
		public function rw_check_termdates_include(): bool
		{
			// Ignore this, if the Term Dates page (under Information) doesn't exist
			if (NULL === get_page_by_path('information/term-dates')) {
				return false;
			}

			// CODE Nicked from here: https://github.com/wpmetabox/meta-box/blob/master/demo/include-by-ID-or-page-template.php
			// Always include in the frontend to make helper function work
			if (!is_admin()) {
				return true;
			}

			// Always include for ajax
			if (defined('DOING_AJAX') && DOING_AJAX) {
				return true;
			}

			// Check for post IDs
			$checked_post_IDs = [];
			array_push($checked_post_IDs, get_page_by_path('information/term-dates')->ID);

			if (isset($_GET['post'])) {
				$post_id = intval($_GET['post']);
			} elseif (isset($_POST['post_ID'])) {
				$post_id = intval($_POST['post_ID']);
			} else {
				$post_id = false;
			}

			$post_id = (int)$post_id;

			if (in_array($post_id, $checked_post_IDs)) {
				return true;
			}

			// If no condition matched
			return false;
		}

		/**
		 * @return array
		 */
		function setupTerms()
		{

			$fields = [];

			foreach ($this->years as $year) :
				foreach ($this->termsinYear($year) as $term) :
					array_push($fields, $this->setupTerm($term));
				endforeach;
			endforeach;

			return $fields;
		}

		/**
		 * @param int $year
		 *
		 * @return array
		 */
		public function termsinYear(int $year): array
		{

			$this_year = (int)$year;
			$next_year = $this_year + 1;

			return [
				'Michaelmas Term (' . $this_year . ')',
				'Lent Term (' . $next_year . ')',
				'Summer Term (' . $next_year . ')',
			];

		}

		/**
		 * @param string $name
		 *
		 * @return array
		 */
		public function setupTerm(string $name): array
		{

			$id = strtolower(str_replace(' ', '-', $name));

			return [
				'name'       => __($name, 'cranleigh-2016'),
				'id'         => self::$meta_prefix . $id,
				'type'       => 'group',
				'clone'      => true,
				'sort_clone' => true,
				'desc'       => 'The rows in the table for ' . $name,
				'fields'     => [
					[
						'name' => 'Title',
						'id'   => self::$meta_prefix . $id . '_title',
						'type' => 'text',
					],
					[
						'name' => 'Value',
						'id'   => self::$meta_prefix . $id . '_value',
						'type' => 'text',
					],
				],
			];
		}

		/**
		 *
		 */
		public function settings_init()
		{
			register_setting('cranleigh-terms-plugin', 'settings');
			add_settings_section(
				'term-dates-section',
				__('Term Dates ISAMS Settings', 'cranleigh-2016'),
				[$this, 'settings_callback'],
				'cranleigh-terms-plugin'
			);

			add_settings_field(
				'term-dates-field',
				__('Pill', 'cranleigh-2016'),
				'cranleigh-terms-plugin',
				'terms-dates-section',
				[
					'label_for'   => 'term-dates-field',
					'class'       => 'class',
					'custom_data' => 'custom'
				]
			);
		}


	}
