<?php
/**
 * Plugin Name: GatherPress Seasons demo data helper
 * Description: Generates demo data for the plugin.
 * Version:     0.1.0
 * Author:      GatherPress Seasons
 */

require_once '/wordpress/wp-load.php';

// Season 1
$post_id = wp_insert_post(
	array(
		'post_type'    => 'gatherpress_season',
		'post_title'   => '2025 Spring Season',
		'post_content' => 'The spring theatre season running from January through June 2025.',
		'post_status'  => 'publish',
	)
);
if ( is_wp_error( $post_id ) ) {
	error_log( 'Error creating season: ' . $post_id->get_error_message() );
} else {
	$event = new \GatherPress\Core\Event( $post_id );
	$event->save_datetimes(
		array(
			'datetime_start' => '2025-01-01 00:00:00',
			'datetime_end'   => '2025-06-30 23:59:59',
			'timezone'       => 'Europe/Berlin',
		)
	);
}

// Season 2
$post_id = wp_insert_post(
	array(
		'post_type'    => 'gatherpress_season',
		'post_title'   => '2025 Autumn Season',
		'post_content' => 'The autumn theatre season running from July through December 2025.',
		'post_status'  => 'publish',
	)
);
if ( is_wp_error( $post_id ) ) {
	error_log( 'Error creating season: ' . $post_id->get_error_message() );
} else {
	$event = new \GatherPress\Core\Event( $post_id );
	$event->save_datetimes(
		array(
			'datetime_start' => '2025-07-01 00:00:00',
			'datetime_end'   => '2025-12-31 23:59:59',
			'timezone'       => 'Europe/Berlin',
		)
	);
}

// Season 3
$post_id = wp_insert_post(
	array(
		'post_type'    => 'gatherpress_season',
		'post_title'   => '2026 Spring Season',
		'post_content' => 'The spring theatre season running from January through June 2026.',
		'post_status'  => 'publish',
	)
);
if ( is_wp_error( $post_id ) ) {
	error_log( 'Error creating season: ' . $post_id->get_error_message() );
} else {
	$event = new \GatherPress\Core\Event( $post_id );
	$event->save_datetimes(
		array(
			'datetime_start' => '2026-01-01 00:00:00',
			'datetime_end'   => '2026-06-30 23:59:59',
			'timezone'       => 'Europe/Berlin',
		)
	);
}

// Season 4
$post_id = wp_insert_post(
	array(
		'post_type'    => 'gatherpress_season',
		'post_title'   => '2026 Autumn Season',
		'post_content' => 'The autumn theatre season running from July through December 2026.',
		'post_status'  => 'publish',
	)
);
if ( is_wp_error( $post_id ) ) {
	error_log( 'Error creating season: ' . $post_id->get_error_message() );
} else {
	$event = new \GatherPress\Core\Event( $post_id );
	$event->save_datetimes(
		array(
			'datetime_start' => '2026-07-01 00:00:00',
			'datetime_end'   => '2026-12-31 23:59:59',
			'timezone'       => 'Europe/Berlin',
		)
	);
}

// Season 5
$post_id = wp_insert_post(
	array(
		'post_type'    => 'gatherpress_season',
		'post_title'   => '2027 Spring Season',
		'post_content' => 'The spring theatre season running from January through June 2027.',
		'post_status'  => 'publish',
	)
);
if ( is_wp_error( $post_id ) ) {
	error_log( 'Error creating season: ' . $post_id->get_error_message() );
} else {
	$event = new \GatherPress\Core\Event( $post_id );
	$event->save_datetimes(
		array(
			'datetime_start' => '2027-01-01 00:00:00',
			'datetime_end'   => '2027-06-30 23:59:59',
			'timezone'       => 'Europe/Berlin',
		)
	);
}

flush_rewrite_rules();

error_log( 'Demo data import complete.' );
