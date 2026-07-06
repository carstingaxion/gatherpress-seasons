<?php
/**
 * Main plugin controller that manages the block in the system.
 *
 * @package GatherPress_Seasons
 */

declare(strict_types=1);

namespace GatherPress_Seasons;

use GatherPress\Core;

/**
 * Main plugin class using Singleton pattern.
 *
 * @since 0.1.0
 */
class Block {

	use Core\Traits\Singleton;

	/**
	 * Constructor for the Block class.
	 *
	 * Initializes and sets up various components of the plugin.
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
		add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_variation_assets' ) );

		// Setup starter patterns as the blocks provided via the modal on opening a new post.
		// add_filter( 'gatherpress_event_starter_patterns', array( $this, 'setup_starter_patterns' ), 10, 2 ); // phpcs:ignore Squiz.PHP.CommentedOutCode.Found .
		add_action( 'init', array( $this, 'register_starter_patterns_natively' ) );
	}

	/**
	 * Enqueues the editor script that registers a block variation of gatherpress/venue to be used as "Production" context block.
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

		$post_type     = Setup::POST_TYPE_NAME;
		$post_type_obj = get_post_type_object( $post_type );

		if ( ! $post_type_obj ) {
			return;
		}

		wp_enqueue_script(
			'gatherpress-seasons-variation',
			plugins_url( 'build/variation.js', dirname( __DIR__, 1 ) ),
			$asset['dependencies'],
			(string) $asset['version'],
			true
		);
		wp_localize_script(
			'gatherpress-seasons-variation',
			'gatherpressSeasons',
			array(
				'singular' => $post_type_obj->labels->singular_name,
				'plural'   => $post_type_obj->name,
			)
		);
		wp_set_script_translations(
			'gatherpress-seasons-variation',
			'gatherpress-seasons'
		);
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
	public function setup_starter_patterns( array $patterns, array $post_types ): array { // phpcs:ignore Generic.CodeAnalysis.UnusedFunctionParameter.FoundAfterLastUsed, VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
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

		$post_type = Setup::POST_TYPE_NAME;
		$pattern   = array(
			'name'        => 'gatherpress-seasons/starter',
			'title'       => __( 'Seasons Starter', 'gatherpress-seasons' ),
			'description' => __( 'A starter pattern for seasons.', 'gatherpress-seasons' ),
			'post_types'  => array( $post_type ),
			'content'     => '<!-- wp:paragraph --><p>' . esc_html__( 'This is a starter pattern for seasons. Customize it to fit your needs!', 'gatherpress-seasons' ) . '</p><!-- /wp:paragraph -->',
		);
		\register_block_pattern(
			$pattern['name'],
			array(
				'title'       => $pattern['title'] ?? '',
				'description' => $pattern['description'] ?? '',
				'content'     => $pattern['content'] ?? '',
				'blockTypes'  => array( 'core/post-content' ),
				'postTypes'   => array( $post_type ),
				'source'      => 'plugin',
			)
		);
	}
}
