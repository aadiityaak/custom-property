<?php
/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://velocitydeveloper.com
 * @since      1.0.0
 *
 * @package    Custom_Plugin
 * @subpackage Custom_Plugin/includes
 */

use Kirki\Util\Helper;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Do not proceed if Kirki does not exist.
if ( ! class_exists( 'Kirki' ) ) {
	return;
}

Kirki::add_config(
	'custom_plugin_config',
	[
		'option_type' => 'theme_mod',
		'capability'  => 'manage_options',
	]
);

/**
 * Add a panel.
 *
 * @link https://docs.themeum.com/kirki/getting-started/panels-sections/
 */
new \Kirki\Panel(
	'custom_plugin_panel',
	[
		'priority'    => 10,
		'title'       => esc_html__( 'Custom Plugin Panel', 'custom-plugin' ),
		'description' => esc_html__( 'Contains sections for all Custom Plugin controls.', 'custom-plugin' ),
	]
);

/**
 * Add Sections.
 *
 * We'll be doing things a bit differently here, just to demonstrate an example.
 * We're going to define 1 section per control-type just to keep things clean and separate.
 *
 * @link https://docs.themeum.com/kirki/getting-started/panels-sections/
 */
$sections = [
	'custom'      => [ esc_html__( 'Custom', 'custom-plugin' ), '' ],
];

foreach ( $sections as $section_id => $section ) {
	$section_args = [
		'title'       => $section[0],
		'description' => $section[1],
		'panel'       => 'custom_plugin_panel',
	];
	if ( isset( $section[2] ) ) {
		$section_args['type'] = $section[2];
	}
	new \Kirki\Section( str_replace( '-', '_', $section_id ) . '_section', $section_args );
}

new \Kirki\Field\Background(
	[
		'settings'    => 'custom_plugin_background',
		'label'       => esc_html__( 'Custom Background Control', 'custom-plugin' ),
		'description' => esc_html__( 'Custom Background conrols are pretty complex! (but useful if properly used)', 'custom-plugin' ),
		'section'     => 'custom_section',
		'default'     => [
			'background-color'      => 'rgba(20,20,20,.8)',
			'background-image'      => '',
			'background-repeat'     => 'repeat',
			'background-position'   => 'center center',
			'background-size'       => 'cover',
			'background-attachment' => 'scroll',
		],
	]
);