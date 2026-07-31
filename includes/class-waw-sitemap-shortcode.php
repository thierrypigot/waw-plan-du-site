<?php
/**
 * Shortcode [waw_sitemap_page].
 */

defined( 'ABSPATH' ) || exit;

class WAW_Sitemap_Shortcode {

	public static function register(): void {
		add_shortcode( 'waw_sitemap_page', array( __CLASS__, 'render' ) );
	}

	/**
	 * La normalisation (défauts, whitelist) est déléguée au renderer,
	 * partagée avec le futur bloc Gutenberg.
	 *
	 * @param array|string $atts Attributs du shortcode ('' si aucun).
	 */
	public static function render( $atts ): string {
		return WAW_Sitemap_Renderer::render( is_array( $atts ) ? $atts : array() );
	}
}
