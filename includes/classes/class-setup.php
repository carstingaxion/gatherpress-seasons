<?php
/**
 * Main plugin controller that manages the hierarchical location taxonomy system.
 *
 * @package GatherPress_Seasons
 */

declare(strict_types=1);

namespace GatherPress_Seasons;

use GatherPress\Core;
use GatherPress\Core\Settings;
use GatherPress\Core\Shadow_Source;
use WP_Post_Type;
use WP_Query;

/**
 * Main plugin class using Singleton pattern.
 *
 * @since 0.1.0
 */
class Setup {

	use Core\Traits\Singleton;

	const POST_TYPE_NAME = 'gatherpress_season';

	const TAXONOMY_NAME = '_gatherpress_season';

	/**
	 * Constructor for the Setup class.
	 *
	 * Initializes and sets up various components of the plugin.
	 *
	 * @return void
	 */
	protected function __construct() {
		$this->setup_hooks();
	}

	/**
	 * Set up hooks for various purposes.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	protected function setup_hooks() {

		// Re-label Admin columns & Editor sidebar panel.
		add_filter( 'gatherpress_event_datetime_label', array( $this, 'change_event_datetime_label' ), 10, 2 );
		// ... and load new duration options.
		add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_editor_assets' ) );
		add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_variation_assets' ) );

		add_action( 'init', array( $this, 'load_textdomain' ) );
		// Register seasons post type.
		add_action( 'init', array( $this, 'register_post_type' ) );
		// Register season shadow taxonomy onto events.
		add_action( 'init', array( $this, 'register_post_tax_relations' ), 12 );

		// Add settings sub-page.
		add_filter( 'gatherpress_sub_pages', array( $this, 'setup_sub_page' ) );

		// Setup starter patterns.
		// add_filter( 'gatherpress_event_starter_patterns', array( $this, 'setup_starter_patterns' ), 10, 2 );
		add_action( 'init', array( $this, 'register_starter_patterns_natively' ) );

		// Hook onto "Event ended" action to update the option, which powers the default_term field of the taxonomy.
		add_action( 'gatherpress_event_ended', array( $this, 'update_default_term_on_season_end' ) );
	}

	/**
	 * Load the plugin's text domain for translations.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	public function load_textdomain(): void {
		\load_plugin_textdomain(
			'gatherpress-seasons',
			false,
			'gatherpress-seasons/languages'
		);
	}

	/**
	 * Returns the post type slug localized for the site language and sanitized as URL part.
	 *
	 * Do not use this directly, use get( 'venues_url' ) instead.
	 *
	 * This method switches to the sites default language and gets the translation of 'venues' for the loaded locale.
	 * After that, the method sanitizes the string to be safely used within an URL,
	 * by removing accents, replacing special characters and replacing whitespace with dashes.
	 *
	 * @since 0.34.0
	 *
	 * @return string
	 */
	protected function get_localized_post_type_slug(): string {
		$switched_locale = switch_to_locale( get_locale() );
		$slug            = __( 'Season', 'gatherpress-seasons' );
		$slug            = sanitize_title( $slug );

		if ( $switched_locale ) {
			restore_previous_locale();
		}

		return $slug;
	}

	/**
	 * Build taxonomy labels from a post type's labels.
	 *
	 * Reuses semantically equivalent post type labels whenever possible and
	 * generates taxonomy-specific labels as fallbacks.
	 *
	 * @param string $post_type Post type name.
	 * @return array
	 */
	protected function get_shadow_taxonomy_labels( string $post_type ): array {
		$post_type_object = get_post_type_object( $post_type );

		if ( ! $post_type_object instanceof WP_Post_Type ) {
			return array(
				'name'          => $post_type,
				'singular_name' => $post_type,
			);
		}

		$pt = $post_type_object->labels;

		/*
		* Taxonomy label => equivalent post type label.
		*/
		$equivalents = array(
			'name'                  => 'name',
			'singular_name'         => 'singular_name',
			'menu_name'             => 'menu_name',
			'all_items'             => 'all_items',
			'search_items'          => 'search_items',
			'view_item'             => 'view_item',
			'not_found'             => 'not_found',
			'item_link'             => 'item_link',
			'item_link_description' => 'item_link_description',
		);

		$labels = array();

		foreach ( $equivalents as $taxonomy_label => $post_type_label ) {
			if ( ! empty( $pt->{$post_type_label} ) ) {
				$labels[ $taxonomy_label ] = $pt->{$post_type_label};
			}
		}

		$name     = $labels['name'] ?? $pt->name ?? $post_type;
		$singular = $labels['singular_name'] ?? $pt->singular_name ?? $post_type;

		/*
		* Taxonomy-only labels.
		*/
		$labels += array(
			'popular_items'              => sprintf(
				/* translators: %s is replaced with the plural name of the taxonomy, e.g. "Seasons". */
				__( 'Popular %s', 'gatherpress-seasons' ),
				$name
			),
			'edit_item'                  => sprintf(
				/* translators: %s is replaced with the singular name of the taxonomy, e.g. "Season". */
				__( 'Edit %s', 'gatherpress-seasons' ),
				$singular
			),
			'update_item'                => sprintf(
				/* translators: %s is replaced with the singular name of the taxonomy, e.g. "Season". */
				__( 'Update %s', 'gatherpress-seasons' ),
				$singular
			),
			'add_new_item'               => sprintf(
				/* translators: %s is replaced with the singular name of the taxonomy, e.g. "Season". */
				__( 'Add New %s', 'gatherpress-seasons' ),
				$singular
			),
			'new_item_name'              => sprintf(
				/* translators: %s is replaced with the singular name of the taxonomy, e.g. "Season". */
				__( 'New %s Name', 'gatherpress-seasons' ),
				$singular
			),
			'separate_items_with_commas' => sprintf(
				/* translators: %s is replaced with the plural name of the taxonomy, e.g. "Seasons". */
				__( 'Separate %s with commas', 'gatherpress-seasons' ),
				lcfirst( $name )
			),
			'add_or_remove_items'        => sprintf(
				/* translators: %s is replaced with the plural name of the taxonomy, e.g. "Seasons". */
				__( 'Add or remove %s', 'gatherpress-seasons' ),
				lcfirst( $name )
			),
			'choose_from_most_used'      => sprintf(
				/* translators: %s is replaced with the plural name of the taxonomy, e.g. "Seasons". */
				__( 'Choose from the most used %s', 'gatherpress-seasons' ),
				lcfirst( $name )
			),
			'parent_item'                => sprintf(
				/* translators: %s is replaced with the singular name of the taxonomy, e.g. "Season". */
				__( 'Parent %s', 'gatherpress-seasons' ),
				$singular
			),
			'parent_item_colon'          => sprintf(
				/* translators: %s is replaced with the singular name of the taxonomy, e.g. "Season". */
				__( 'Parent %s:', 'gatherpress-seasons' ),
				$singular
			),
		);

		return $labels;
	}

	/**
	 * Filter to register the shadow taxonomy with custom arguments.
	 *
	 * This method is hooked to the 'gatherpress_shadow_taxonomy_args' filter, which is triggered when registering a shadow taxonomy for a post type.
	 * The method checks if the post type matches the one used for seasons, and if so, it modifies the taxonomy arguments to set custom labels, show in quick edit, show in UI, and default term.
	 *
	 * @since 0.1.0
	 *
	 * @param array  $args The original taxonomy arguments.
	 * @param string $post_type The post type for which the shadow taxonomy is being registered.
	 *
	 * @return array The modified taxonomy arguments for the season shadow taxonomy.
	 */
	public function register_taxonomy_args( array $args, string $post_type ) {
		if ( self::POST_TYPE_NAME === $post_type ) {
			$args['labels']             = $this->get_shadow_taxonomy_labels( $post_type );
			$args['show_in_quick_edit'] = true;
			$args['show_ui']            = true; // Needed to show the taxonomy metabox in the editor.
			$args['show_in_menu']       = false; // Correction after show_ui.
			$args['default_term']       = maybe_unserialize( get_option( sprintf( 'prepared_default_term_%s', self::TAXONOMY_NAME ) ) );
		}
		return $args;
	}

	/**
	 * Register the custom post type for seasons.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	public function register_post_type(): void {
		add_filter( 'gatherpress_shadow_taxonomy_args', array( $this, 'register_taxonomy_args' ), 10, 2 );

		$settings     = Settings::get_instance();
		$rewrite_slug = $settings->get( 'seasons_url' );

		$labels = array(
			'name'                     => __( 'Seasons', 'gatherpress-seasons' ),
			'singular_name'            => __( 'Season', 'gatherpress-seasons' ),
			'add_new'                  => __( 'Add New', 'gatherpress-seasons' ),
			'add_new_item'             => __( 'Add New Season', 'gatherpress-seasons' ),
			'edit_item'                => __( 'Edit Season', 'gatherpress-seasons' ),
			'new_item'                 => __( 'New Season', 'gatherpress-seasons' ),
			'view_item'                => __( 'View Season', 'gatherpress-seasons' ),
			'view_items'               => __( 'View Seasons', 'gatherpress-seasons' ),
			'search_items'             => __( 'Search Seasons', 'gatherpress-seasons' ),
			'not_found'                => __( 'No seasons found', 'gatherpress-seasons' ),
			'not_found_in_trash'       => __( 'No seasons found in Trash', 'gatherpress-seasons' ),
			'parent_item_colon'        => __( 'Parent Season:', 'gatherpress-seasons' ),
			// 'all_items'                => __( 'All Seasons', 'gatherpress-seasons' ),
			'all_items'                => __( 'Seasons', 'gatherpress-seasons' ),
			'archives'                 => __( 'Season Archives', 'gatherpress-seasons' ),
			'attributes'               => __( 'Season Attributes', 'gatherpress-seasons' ),
			'insert_into_item'         => __( 'Insert into season', 'gatherpress-seasons' ),
			'uploaded_to_this_item'    => __( 'Uploaded to this season', 'gatherpress-seasons' ),
			'featured_image'           => __( 'Season Poster', 'gatherpress-seasons' ),
			'set_featured_image'       => __( 'Set season poster', 'gatherpress-seasons' ),
			'remove_featured_image'    => __( 'Remove season poster', 'gatherpress-seasons' ),
			'use_featured_image'       => __( 'Use as season poster', 'gatherpress-seasons' ),
			'menu_name'                => __( 'Seasons', 'gatherpress-seasons' ),
			'filter_items_list'        => __( 'Filter seasons list', 'gatherpress-seasons' ),
			'filter_by_date'           => __( 'Filter seasons by date', 'gatherpress-seasons' ),
			'items_list_navigation'    => __( 'Seasons list navigation', 'gatherpress-seasons' ),
			'items_list'               => __( 'Seasons list', 'gatherpress-seasons' ),
			'item_published'           => __( 'Season published.', 'gatherpress-seasons' ),
			'item_published_privately' => __( 'Season published privately.', 'gatherpress-seasons' ),
			'item_reverted_to_draft'   => __( 'Season reverted to draft.', 'gatherpress-seasons' ),
			'item_trashed'             => __( 'Season moved to Trash.', 'gatherpress-seasons' ),
			'item_scheduled'           => __( 'Season scheduled.', 'gatherpress-seasons' ),
			'item_updated'             => __( 'Season updated.', 'gatherpress-seasons' ),
			'item_link'                => __( 'Season Link', 'gatherpress-seasons' ),
			'item_link_description'    => __( 'A link to a season.', 'gatherpress-seasons' ),
		);

		\register_post_type(
			self::POST_TYPE_NAME,
			array(
				'labels'       => $labels,
				'supports'     => array(
					'title',
					'editor',
					'thumbnail',
					'excerpt',
					'custom-fields',
					'revisions',
					'gatherpress-event-date', // @see
					'gatherpress-shadow-source', // @see https://github.com/GatherPress/gatherpress/tree/develop/docs/developer/post-type-supports#gatherpress-shadow-source
				),
				'public'       => true,
				'show_in_menu' => 'edit.php?post_type=gatherpress_event',
				'show_in_rest' => true, // This in combination with  'supports' => array('editor') enables the Gutenberg editor.
				'hierarchical' => true, // (Note from Subsites plugin: Important for rewriting to work with 'parent' PT.)
				'description'  => '',
				'menu_icon'    => 'dashicons-clock',

				'rewrite'      => [
					'slug'       => $rewrite_slug,
					'with_front' => false,      // Defaults to true.
					// 'feeds'   => false,      // Defaults to 'has_archive'.
					// 'pages'   => false,      // Defaults to true.
					// 'ep_mask' => 'EP_NONE',  // Defaults to EP_PERMALINK.

				],

				'has_archive'  => true,
				'can_export'   => true,
			)
		);
	}

	/**
	 * Register the custom post type for seasons.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	public function register_post_tax_relations(): void {
		\register_taxonomy_for_object_type( self::TAXONOMY_NAME, 'gatherpress_event' );
		\register_taxonomy_for_object_type( self::TAXONOMY_NAME, 'gatherpress_play' );
	}

	/**
	 * Change the label for the event datetime column to "Period".
	 *
	 * @since 0.1.0
	 *
	 * @uses 'gatherpress_event_datetime_label' filter to modify the label for the event datetime column in the admin list table.
	 * @see  https://github.com/GatherPress/gatherpress/tree/develop/docs/developer/hooks/gatherpress_event_datetime_label.md
	 *
	 * @param string $label The original label for the event datetime column.
	 * @param string $post_type The post type for which to modify the label.
	 *
	 * @return string The modified label for the event datetime column.
	 */
	public function change_event_datetime_label( string $label, string $post_type ): string {
		if ( self::POST_TYPE_NAME === $post_type ) {
			return __( 'Period', 'gatherpress-seasons' );
		}
		return $label;
	}

	/**
	 * Enqueues the editor script that registers label filter for the sidebar.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	public function enqueue_editor_assets(): void {

		// Guard to only enqueue on the season edit screen.
		if ( self::POST_TYPE_NAME !== get_current_screen()->post_type ) {
			return;
		}

		$asset_file = GATHERPRESS_SEASONS_CORE_PATH . '/build/index.asset.php';
		if ( ! file_exists( $asset_file ) ) {
			return;
		}

		/**
		 * The asset file is expected to return an array with 'dependencies' and 'version' keys.
		 *
		 * @var array{dependencies: string[], version: string} $asset
		 */
		$asset = include $asset_file; // phpcs:ignore WordPressVIPMinimum.Files.IncludingFile.UsingVariable

		if ( ! is_array( $asset ) || ! isset( $asset['dependencies'], $asset['version'] ) ) {
			return;
		}

		wp_enqueue_script(
			'gatherpress-seasons-editor',
			plugins_url( 'build/index.js', dirname( __DIR__, 1 ) ),
			$asset['dependencies'],
			(string) $asset['version'],
			true
		);

		wp_set_script_translations(
			'gatherpress-seasons-editor',
			'gatherpress-seasons'
		);
	}

	/**
	 * Enqueues the editor script that registers the block variation for the gatherpress/venue block.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	public function enqueue_variation_assets(): void {
		$asset_file = GATHERPRESS_SEASONS_CORE_PATH . '/build/variation.asset.php';
		if ( ! file_exists( $asset_file ) ) {
			return;
		}

		/**
		 * The asset file is expected to return an array with 'dependencies' and 'version' keys.
		 *
		 * @var array{dependencies: string[], version: string} $asset
		 */
		$asset = include $asset_file; // phpcs:ignore WordPressVIPMinimum.Files.IncludingFile.UsingVariable

		if ( ! is_array( $asset ) || ! isset( $asset['dependencies'], $asset['version'] ) ) {
			return;
		}

		wp_enqueue_script(
			'gatherpress-seasons-variation',
			plugins_url( 'build/variation.js', dirname( __DIR__, 1 ) ),
			$asset['dependencies'],
			(string) $asset['version'],
			true
		);

		wp_set_script_translations(
			'gatherpress-seasons-variation',
			'gatherpress-seasons'
		);
	}


	/**
	 * Adds a sub-page for "Seasons" to the existing sub-pages array.
	 *
	 * This function modifies the provided sub-pages array to include a new sub-page
	 * for GatherPress Seasons with specified details such as name, priority, and sections.
	 *
	 * @param array $sub_pages An associative array of existing sub-pages.
	 * @return array Modified array of sub-pages including the new GatherPress Seasons sub-page.
	 */
	public function setup_sub_page( array $sub_pages ): array {

		$current_sub_pages    = $sub_pages['theater']['sections'] ?? array();
		$sub_pages['theater'] = array(
			'name'     => __( 'Theater', 'gatherpress-seasons' ),
			'priority' => 10,
			'sections' => array_merge(
				$current_sub_pages,
				array(
					'season_urls' => array(
						'name'        => __( 'Permalinks', 'gatherpress' ),
						'description' => __( 'Change permalink bases.', 'gatherpress' ),
						'options'     => array(
							'seasons_url' => array(
								'labels' => array(
									'name' => __( 'Seasons', 'gatherpress-seasons' ),
								),
								'field'  => array(
									'type'    => 'text',
									'rewrite' => true,
									'options' => array(
										'label'   => __( 'Permalink base of Seasons.', 'gatherpress-seasons' ),
										'default' => $this->get_localized_post_type_slug(),
									),
									'preview' => array(
										'template' => 'url-rewrite-preview',
										'suffix'   => _x(
											'sample-season',
											'URL permalink structure example for seasons',
											'gatherpress-seasons'
										),
									),
								),
							),
						),
					),
				)
			),
		);

		return $sub_pages;
	}

	/**
	 * Set up starter patterns FOR ALL post types using the 'gatherpress-event-date' post_type support.
	 *
	 * @since 0.1.0
	 *
	 * @uses 'gatherpress_event_starter_patterns' filter
	 * @see  https://github.com/GatherPress/gatherpress/blob/develop/docs/developer/hooks/gatherpress_event_starter_patterns.md
	 *
	 * @param  array $patterns   Pattern definitions loaded from the plugin and other sources.
	 * @param  array $post_types Post type slugs declaring gatherpress-event-date support.
	 *
	 * @return array
	 */
	public function setup_starter_patterns( array $patterns, array $post_types ): array {
		$patterns[] = array(
			'name'        => 'gatherpress-seasons/starter',
			'title'       => __( 'Seasons Starter', 'gatherpress-seasons' ),
			'description' => __( 'A starter pattern for seasons.', 'gatherpress-seasons' ),
			'content'     => '<!-- wp:paragraph --><p>' . esc_html__( 'This is a starter pattern for seasons. Customize it to fit your needs!', 'gatherpress-seasons' ) . '</p><!-- /wp:paragraph -->',
		);

		return $patterns;
	}

	/**
	 * Register the starter pattern natively using WordPress's block pattern API.
	 * This is an alternative to using the 'gatherpress_event_starter_patterns' filter and allows the pattern to be available only to selected post types.
	 *
	 * @since 0.1.0
	 *
	 * @return void
	 */
	public function register_starter_patterns_natively(): void {

		$pattern = array(
			'name'        => 'gatherpress-seasons/starter',
			'title'       => __( 'Seasons Starter', 'gatherpress-seasons' ),
			'description' => __( 'A starter pattern for seasons.', 'gatherpress-seasons' ),
			'post_types'  => array( self::POST_TYPE_NAME ),
			'content'     => '<!-- wp:paragraph --><p>' . esc_html__( 'This is a starter pattern for seasons. Customize it to fit your needs!', 'gatherpress-seasons' ) . '</p><!-- /wp:paragraph -->',
		);
		\register_block_pattern(
			$pattern['name'],
			array(
				'title'       => $pattern['title'] ?? '',
				'description' => $pattern['description'] ?? '',
				'content'     => $pattern['content'] ?? '',
				'blockTypes'  => array( 'core/post-content' ),
				'postTypes'   => array( self::POST_TYPE_NAME ),
				'source'      => 'plugin',
			)
		);
	}

	/**
	 * Update the option, that powers the default_term field of the taxonomy, when a season ends.
	 *
	 * This method is hooked to the 'gatherpress_event_ended' action, which is triggered when an event-supporting post ends.
	 * This action is not part of gatherpress core, it's triggered by the "GatherPress Cache Invalidation Hooks" plugin.
	 *
	 * @since 0.1.0
	 *
	 * @param int $event_id The ID of the event-supporting post that ended.
	 *                      Can be an event, a season, a play or anything else.
	 *
	 * @return void
	 */
	public function update_default_term_on_season_end( int $event_id ): void {
		$post_type = get_post_type( $event_id );
		if ( self::POST_TYPE_NAME !== $post_type ) {
			return;
		}

		// Look for the next season.
		$new_season  = new WP_Query(
			array(
				'post_type'               => self::POST_TYPE_NAME,
				'posts_per_page'          => 1,
				'gatherpress_event_query' => 'upcoming', // gatherpress core query var.
				'include_unfinished'      => true, // gatherpress core query var.
				'post_status'             => 'publish',
				'order'                   => 'ASC',
				'no_found_rows'           => false,
				'update_post_meta_cache'  => false,
				'update_post_term_cache'  => false,
			)
		);
		$option_name = sprintf( 'prepared_default_term_%s', self::TAXONOMY_NAME );

		if ( ! empty( $new_season->posts ) ) {
			$shadow_source = Shadow_Source::get_instance();
			$season_post   = $new_season->posts[0];
			$season_term   = get_term_by(
				'slug',
				$shadow_source->term_slug_from_post_name( $season_post->post_name ),
				self::TAXONOMY_NAME,
				ARRAY_A
			);
			$save_data     = array(
				'name' => $season_term['name'],
				'slug' => $season_term['slug'],
			);
			update_option( $option_name, $save_data );
		} else {
			// No upcoming seasons, delete the option.
			delete_option( $option_name );
		}
	}
}
