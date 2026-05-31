/**
 * WordPress dependencies.
 */
import { addFilter } from '@wordpress/hooks';
import { __ } from '@wordpress/i18n';

// Relabel the editor sidebar panel title for the same post type.
addFilter(
	'gatherpress.eventSettingsPanelTitle',
	'gatherpress-seasons/relabel',
	( title, pt ) =>
		'gatherpress_season' === pt
			? __( 'Period', 'gatherpress-seasons' )
			: title
);

// Define new duration options for the seasons post type.
const ONE_DAY = 24;
const ONE_WEEK = ONE_DAY * 7;
const ONE_MONTH = ONE_DAY * 30;
const THREE_MONTHS = ONE_MONTH * 3;
const SIX_MONTHS = ONE_MONTH * 6;

// Filter the duration options for the seasons post type.
addFilter(
	'gatherpress.durationOptions',
	'gatherpress/durationOptionsTest',
	function () {
		return [
			{
				label: __( '6 months', 'gatherpress-seasons' ),
				value: SIX_MONTHS,
			},
			{
				label: __( '3 months', 'gatherpress-seasons' ),
				value: THREE_MONTHS,
			},
			{
				label: __( '1 month', 'gatherpress-seasons' ),
				value: ONE_MONTH,
			},
			{
				label: __( '1 week', 'gatherpress-seasons' ),
				value: ONE_WEEK,
			},
			{
				label: __( 'Set an end time…', 'gatherpress' ),
				value: false,
			},
		];
	}
);
