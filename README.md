# GatherPress Seasons

**Contributors:** carstenbach  
**Tags:** theater, seasons, gatherpress  
**Tested up to:** 6.9  
**Stable tag:** 0.2.2  
**License:** GPLv2 or later  
**License URI:** https://www.gnu.org/licenses/gpl-2.0.html  

[![Playground Demo Link](https://img.shields.io/badge/WordPress_Playground-blue?logo=wordpress&logoColor=%23fff&labelColor=%233858e9&color=%233858e9)](https://playground.wordpress.net/?blueprint-url=https://raw.githubusercontent.com/carstingaxion/gatherpress-seasons/main/.wordpress-org/blueprints/blueprint.json) [![Build, test & measure](https://github.com/carstingaxion/gatherpress-seasons/actions/workflows/build-test-measure.yml/badge.svg?branch=main)](https://github.com/carstingaxion/gatherpress-seasons/actions/workflows/build-test-measure.yml)


## Description

GatherPress Seasons extends [GatherPress](https://gatherpress.org/) for **theater and performing-arts use cases**. It adds a dedicated **Season** content type and wires it into GatherPress's event system so that every season spans a defined period, carries a two-way relationship with both regular GatherPress events and Productions (from the companion GatherPress Productions plugin), and always keeps the shadow taxonomy's default term pointed at the currently active season.

### What it does

**Season post type (`gatherpress_season`)**  
Registers a public, block-editor-enabled post type called *Season* (hierarchical, with archive, configurable permalink base). Seasons support a featured image — labelled *Season Poster* in the UI — as well as title, content, excerpt, custom fields, and revisions. The post type is displayed under the GatherPress Events menu in the admin.

**Period date instead of "Event date"**  
On Season posts, GatherPress's built-in date picker and admin list column are re-labelled *Period* (via both PHP and a JavaScript filter on the editor sidebar panel title). Season-appropriate duration presets replace the standard event duration options: *6 months*, *3 months*, *1 month*, *1 week*, and a manual "Set an end time…" fallback.

**Shadow taxonomy linking seasons to events and productions**  
A private shadow taxonomy (`_gatherpress_season`) is registered on both `gatherpress_event` and `gatherpress_play` (the Productions post type). This wires individual events and productions back to the season they belong to, enabling queries across all content for a given season.

**Auto-rotating default season term**  
When a season's period ends (triggered by the `gatherpress_event_ended` action), the plugin queries for the next upcoming published season and updates a `prepared_default_term__gatherpress_season` option. The shadow taxonomy reads this option as its `default_term`, so newly created events and productions are automatically assigned to the current active season without any manual selection.

**Block variation: Season Details**  
Registers a JavaScript block variation of `gatherpress/venue` named *Season* (or whatever the singular post-type label is). The variation sets `sourcePostType` to `gatherpress_season` and ships default inner blocks (linked post title + linked featured image at heading level 3), giving editors a ready-made Season context block in the inserter.

**Re-labelled editor UI**  
A JavaScript filter (`gatherpress.eventSettingsPanelTitle`) renames the *Event Settings* sidebar panel to *Period* when editing a Season, and a second filter (`gatherpress.durationOptions`) replaces the standard duration picker with season-length presets, keeping the authoring experience appropriate for multi-month runs.

**Settings sub-page**  
Adds a *Theater* section under the GatherPress settings screen (merging with any section added by companion plugins) with a *Permalinks* option so site administrators can customise the URL base for the Seasons archive (defaults to the translated word for "Season").

**Starter block pattern**  
Registers a `gatherpress-seasons/starter` block pattern scoped to the Season post type, giving authors an optional starting layout when creating a new season.

## Requirements

- WordPress 6.4 or later
- PHP 7.4 or later
- [GatherPress](https://gatherpress.org/) 0.34.0-alpha-2 or later

## Installation

1. Upload the plugin files to `/wp-content/plugins/gatherpress-seasons`.
2. Activate the plugin via the **Plugins** screen.


## Frequently Asked Questions

### Does this work without GatherPress?

No.

## Changelog

All notable changes to this project will be documented in the [CHANGELOG.md](CHANGELOG.md).

## License

This plugin is licensed under the [GPLv2 or later](https://www.gnu.org/licenses/gpl-2.0.html).
