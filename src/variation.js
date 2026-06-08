/**
 * WordPress dependencies.
 */
import { registerBlockVariation } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';

/**
 * Extend 'gatherpress/venue' to provide production context.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-variations/
 */
registerBlockVariation( 'gatherpress/venue', {
	name: 'gatherpress-seasons/details',
	title: __( 'Season', 'gatherpress' ),
	description: __( 'Provides season context.', 'gatherpress' ),
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
				isLink: true
			}
		],
		[
			'core/post-featured-image',
			{
				isLink: true
			}
		],
	],
	scope: [ 'inserter', 'block' ], // Defaults to 'block' and 'inserter'.
	example: {} // Disabled like the original 'core/post-terms' block.
} );
