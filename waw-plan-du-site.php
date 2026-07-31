<?php
/**
 * Plugin Name:       WAW Plan du site
 * Plugin URI:        https://github.com/thierrypigot/waw-plan-du-site
 * Description:       Plan de site HTML accessible via le shortcode [waw_sitemap_page] : filtrage par type de contenu ou taxonomie, tri, profondeur, exclusions.
 * Version:           1.1.0
 * Requires at least: 6.7
 * Requires PHP:      8.1
 * Author:            WeAre[WP]
 * Author URI:        https://www.wearewp.pro
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       waw-plan-du-site
 */

defined( 'ABSPATH' ) || exit;

define( 'WAW_SITEMAP_VERSION', '1.1.0' );
define( 'WAW_SITEMAP_DIR', plugin_dir_path( __FILE__ ) );

require_once WAW_SITEMAP_DIR . 'plugin-update-checker/plugin-update-checker.php';
require_once WAW_SITEMAP_DIR . 'includes/class-waw-sitemap-renderer.php';
require_once WAW_SITEMAP_DIR . 'includes/class-waw-sitemap-shortcode.php';
require_once WAW_SITEMAP_DIR . 'includes/class-waw-sitemap-admin.php';
require_once WAW_SITEMAP_DIR . 'includes/class-waw-sitemap-cache.php';

$waw_sitemap_update_checker = \YahnisElsts\PluginUpdateChecker\v5\PucFactory::buildUpdateChecker(
	'https://github.com/thierrypigot/waw-plan-du-site/',
	__FILE__,
	'waw-plan-du-site'
);
$waw_sitemap_update_checker->getVcsApi()->enableReleaseAssets();

add_action( 'init', array( 'WAW_Sitemap_Shortcode', 'register' ) );
add_action( 'admin_menu', array( 'WAW_Sitemap_Admin', 'register' ) );
add_action( 'admin_init', array( 'WAW_Sitemap_Admin', 'register_settings' ) );
WAW_Sitemap_Cache::register();
