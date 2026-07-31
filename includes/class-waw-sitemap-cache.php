<?php
/**
 * Cache transient du plan de site (désactivé par défaut, réglage dédié).
 *
 * Invalidation par version : chaque modification de contenu ou de réglages
 * bumpe la version, ce qui change les clés — les anciens transients
 * expirent d'eux-mêmes via leur TTL.
 */

defined( 'ABSPATH' ) || exit;

class WAW_Sitemap_Cache {

	public static function register(): void {
		$events = array(
			'save_post',
			'deleted_post',
			'trashed_post',
			'untrashed_post',
			'created_term',
			'edited_term',
			'delete_term',
			'update_option_waw_sitemap_options',
		);

		foreach ( $events as $event ) {
			add_action( $event, array( __CLASS__, 'bump' ) );
		}
	}

	/**
	 * Clé de cache : attributs normalisés + page courante (la page qui
	 * contient le shortcode est auto-exclue, le HTML en dépend donc).
	 */
	public static function key( array $atts ): string {
		unset( $atts['cache_enabled'] );

		return 'waw_sitemap_' . md5( wp_json_encode( $atts ) . '|' . get_queried_object_id() . '|' . self::version() );
	}

	public static function ttl(): int {
		return (int) apply_filters( 'waw_sitemap_cache_ttl', 12 * HOUR_IN_SECONDS );
	}

	public static function version(): string {
		return (string) get_option( 'waw_sitemap_cache_version', '1' );
	}

	public static function bump(): void {
		update_option( 'waw_sitemap_cache_version', (string) time(), false );
	}
}
