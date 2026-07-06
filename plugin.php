<?php
/**
 * Plugin Name:       GatherPress Seasons
 * Plugin URI:        https://github.com/carstingaxion/gatherpress-seasons
 * Description:       Adds a Season post type to GatherPress with period date, duration presets, auto-rotating default term, and relation to events and productions.
 * Version:           0.2.1
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Requires plugins:  gatherpress
 * Author:            carstenbach
 * License:           GPLv2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       gatherpress-seasons
 * Domain Path:       /languages
 *
 * @package GatherPress_Seasons
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

// Constants.
define( 'GATHERPRESS_SEASONS_VERSION', current( get_file_data( __FILE__, array( 'Version' ), 'plugin' ) ) );
define( 'GATHERPRESS_SEASONS_CORE_PATH', __DIR__ );


/**
 * Adds the GatherPress_Seasons namespace to the autoloader.
 *
 * This function hooks into the 'gatherpress_autoloader' filter and adds the
 * GatherPress_Seasons namespace to the list of namespaces with its core path.
 *
 * @param array<string, string> $namespaces An associative array of namespaces and their paths.
 * @return array<string, string> Modified array of namespaces and their paths.
 */
function gatherpress_seasons_autoloader( array $namespaces ): array {
	$namespaces['GatherPress_Seasons'] = GATHERPRESS_SEASONS_CORE_PATH;

	return $namespaces;
}
add_filter( 'gatherpress_autoloader', 'gatherpress_seasons_autoloader' );

/**
 * Initialize the plugin.
 *
 * Bootstrap function that starts the plugin by initializing the main class.
 *
 * This function hooks into the 'plugins_loaded' action to ensure that
 * the instances are created once all plugins are loaded,
 * only if the GatherPress plugin is active.
 *
 * @since 0.1.0
 * @return void
 */
function gatherpress_seasons_setup(): void {
	if ( defined( 'GATHERPRESS_VERSION' ) ) {
		\GatherPress_Seasons\Setup::get_instance();
		\GatherPress_Seasons\Block::get_instance();
	}
}
add_action( 'plugins_loaded', 'gatherpress_seasons_setup' );


// /**
// * Filters the labels of a specific post type.
// *
// * @param object $labels Object with labels for the post type as member variables.
// * @return object Object with labels for the post type as member variables.
// */
// add_filter('post_type_labels_gatherpress_season', function ( object $labels ) : object {

// 	$_en_labels = array(
// 		'name'                     => 'Chapters',
// 		'singular_name'            => 'Chapter',
// 		'add_new'                  => 'Add New',
// 		'add_new_item'             => 'Add New Chapter',
// 		'edit_item'                => 'Edit Chapter',
// 		'new_item'                 => 'New Chapter',
// 		'view_item'                => 'View Chapter',
// 		'view_items'               => 'View Chapters',
// 		'search_items'             => 'Search Chapters',
// 		'not_found'                => 'No chapters found',
// 		'not_found_in_trash'       => 'No chapters found in Trash',
// 		'parent_item_colon'        => 'Parent Chapter:',
// 		// 'all_items'                => 'All Chapters',
// 		'all_items'                => 'Chapters',
// 		'archives'                 => 'Chapter Archives',
// 		'attributes'               => 'Chapter Attributes',
// 		'insert_into_item'         => 'Insert into chapter',
// 		'uploaded_to_this_item'    => 'Uploaded to this chapter',
// 		'featured_image'           => 'Chapter Poster',
// 		'set_featured_image'       => 'Set chapter poster',
// 		'remove_featured_image'    => 'Remove chapter poster',
// 		'use_featured_image'       => 'Use as chapter poster',
// 		'menu_name'                => 'Chapters',
// 		'filter_items_list'        => 'Filter chapters list',
// 		'filter_by_date'           => 'Filter chapters by date',
// 		'items_list_navigation'    => 'Chapters list navigation',
// 		'items_list'               => 'Chapters list',
// 		'item_published'           => 'Chapter published.',
// 		'item_published_privately' => 'Chapter published privately.',
// 		'item_reverted_to_draft'   => 'Chapter reverted to draft.',
// 		'item_trashed'             => 'Chapter moved to Trash.',
// 		'item_scheduled'           => 'Chapter scheduled.',
// 		'item_updated'             => 'Chapter updated.',
// 		'item_link'                => 'Chapter Link',
// 		'item_link_description'    => 'A link to a chapter.',
// 	);
// 	$_de_labels = array(
// 		'name'                     => 'Kapitel',
// 		'singular_name'            => 'Kapitel',
// 		'add_new'                  => 'Neu hinzufügen',
// 		'add_new_item'             => 'Neues Kapitel hinzufügen',
// 		'edit_item'                => 'Kapitel bearbeiten',
// 		'new_item'                 => 'Neues Kapitel',
// 		'view_item'                => 'Kapitel ansehen',
// 		'view_items'               => 'Kapitel ansehen',
// 		'search_items'             => 'Kapitel durchsuchen',
// 		'not_found'                => 'Keine Kapitel gefunden',
// 		'not_found_in_trash'       => 'Keine Kapitel im Papierkorb gefunden',
// 		'parent_item_colon'        => 'Übergeordnetes Kapitel:',
// 		// 'all_items'             => 'Alle Kapitel',
// 		'all_items'                => 'Kapitel',
// 		'archives'                 => 'Kapitel-Archive',
// 		'attributes'               => 'Kapitel-Attribute',
// 		'insert_into_item'         => 'In Kapitel einfügen',
// 		'uploaded_to_this_item'    => 'Zu diesem Kapitel hochgeladen',
// 		'featured_image'           => 'Kapitel-Poster',
// 		'set_featured_image'       => 'Kapitel-Poster festlegen',
// 		'remove_featured_image'    => 'Kapitel-Poster entfernen',
// 		'use_featured_image'       => 'Als Kapitel-Poster verwenden',
// 		'menu_name'                => 'Kapitel',
// 		'filter_items_list'        => 'Kapitelliste filtern',
// 		'filter_by_date'           => 'Kapitel nach Datum filtern',
// 		'items_list_navigation'    => 'Navigation der Kapitelliste',
// 		'items_list'               => 'Kapitelliste',
// 		'item_published'           => 'Kapitel veröffentlicht.',
// 		'item_published_privately' => 'Kapitel privat veröffentlicht.',
// 		'item_reverted_to_draft'   => 'Kapitel in Entwurf zurückgesetzt.',
// 		'item_trashed'             => 'Kapitel in den Papierkorb verschoben.',
// 		'item_scheduled'           => 'Kapitel geplant.',
// 		'item_updated'             => 'Kapitel aktualisiert.',
// 		'item_link'                => 'Kapitel-Link',
// 		'item_link_description'    => 'Ein Link zu einem Kapitel.',
// 	);
// 	foreach ($_de_labels as $key => $value) {
// 		$labels->{$key} = $value;
// 	}

// 	return $labels;
// } );
