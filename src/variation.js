/**
 * WordPress dependencies.
 */
import { registerBlockVariation } from '@wordpress/blocks';
import { __, sprintf } from '@wordpress/i18n';
import domReady from '@wordpress/dom-ready';

domReady( () => {
	const { singular } = window.gatherpressSeasons;

	/**
	 * Extend 'gatherpress/venue' to provide season context.
	 *
	 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-variations/
	 */
	registerBlockVariation( 'gatherpress/venue', {
		name: 'gatherpress-seasons/details',
		title: singular,
		description: sprintf(
			/* translators: %s: Singular post type label, e.g. "Season". */
			__( 'Provides %s context.', 'gatherpress-seasons' ),
			singular
		),
		icon: 'clock',
		category: 'gatherpress',
		isActive: [ 'sourcePostType' ],
		attributes: {
			sourcePostType: 'gatherpress_season',
		},
		innerBlocks: [
			[
				'core/post-title',
				{
					level: 3,
					isLink: true,
				},
			],
			[
				'core/post-featured-image',
				{
					isLink: true,
				},
			],
		],
		scope: [ 'inserter', 'block' ], // Defaults to 'block' and 'inserter'.
		example: {}, // Disabled like the original 'core/post-terms' block.
	} );
} );
