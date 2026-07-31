<?php
/**
 * Moteur de rendu du plan de site.
 *
 * Indépendant du shortcode : reçoit des arguments bruts, retourne le HTML.
 * Réutilisé tel quel par le bloc Gutenberg (phase 2).
 */

defined( 'ABSPATH' ) || exit;

class WAW_Sitemap_Renderer {

	/**
	 * Correspondance attribut `sort` → valeur `orderby` de WP_Query.
	 */
	private const ORDERBY_MAP = array(
		'post_title' => 'title',
		'menu_order' => 'menu_order',
		'post_name'  => 'name',
		'post_date'  => 'date',
		'ID'         => 'ID',
	);

	private const TITLE_LEVELS = array( 'h2', 'h3', 'h4' );

	public static function defaults(): array {
		$defaults = array(
			'only'          => 'page',
			'sort'          => 'post_title',
			'order'         => 'ASC',
			'display_title' => true,
			'title_level'   => 'h2',
			'sublevel'      => 0,
			'exclude'       => array(),
			'taxonomy'      => '',
			'term'          => '',
			'nav_label'     => __( 'Plan du site', 'waw-plan-du-site' ),
		);

		return (array) apply_filters( 'waw_sitemap_default_atts', $defaults );
	}

	/**
	 * Normalise les attributs bruts (shortcode ou bloc) : whitelist stricte,
	 * types garantis. Toute valeur invalide retombe sur le défaut.
	 */
	public static function normalize( array $raw ): array {
		$defaults = self::defaults();
		$raw      = array_change_key_case( $raw, CASE_LOWER );
		$atts     = array_merge( $defaults, array_intersect_key( $raw, $defaults ) );

		$atts['only'] = array_values( array_filter( array_map(
			'sanitize_key',
			array_map( 'trim', explode( ',', (string) ( is_array( $atts['only'] ) ? implode( ',', $atts['only'] ) : $atts['only'] ) ) )
		) ) );

		$sort         = strtolower( (string) $atts['sort'] );
		$sort         = ( 'id' === $sort ) ? 'ID' : $sort;
		$atts['sort'] = array_key_exists( $sort, self::ORDERBY_MAP ) ? $sort : $defaults['sort'];

		$atts['order'] = ( 'DESC' === strtoupper( (string) $atts['order'] ) ) ? 'DESC' : 'ASC';

		$atts['display_title'] = filter_var( $atts['display_title'], FILTER_VALIDATE_BOOLEAN );

		$level               = strtolower( (string) $atts['title_level'] );
		$atts['title_level'] = in_array( $level, self::TITLE_LEVELS, true ) ? $level : $defaults['title_level'];

		$atts['sublevel'] = max( 0, (int) $atts['sublevel'] );
		$atts['exclude']  = wp_parse_id_list( $atts['exclude'] );
		$atts['taxonomy'] = sanitize_key( (string) $atts['taxonomy'] );
		$atts['term']     = sanitize_title( (string) $atts['term'] );
		$atts['nav_label'] = (string) $atts['nav_label'];

		return (array) apply_filters( 'waw_sitemap_atts', $atts, $raw );
	}

	/**
	 * IDs à exclure : attribut `exclude` + page courante.
	 */
	private static function excluded_ids( array $atts ): array {
		$ids     = $atts['exclude'];
		$current = get_queried_object_id();

		if ( $current > 0 ) {
			$ids[] = $current;
		}

		$ids = array_values( array_unique( array_map( 'intval', $ids ) ) );

		return (array) apply_filters( 'waw_sitemap_excluded_ids', $ids, $atts );
	}

	/**
	 * Un contenu est-il marqué noindex par Yoast, Rank Math ou SEOPress ?
	 * Détection par meta, sans dépendance à ces extensions.
	 */
	private static function is_noindex( int $post_id ): bool {
		$noindex = ( '1' === get_post_meta( $post_id, '_yoast_wpseo_meta-robots-noindex', true ) );

		if ( ! $noindex ) {
			$robots  = get_post_meta( $post_id, 'rank_math_robots', true );
			$noindex = is_array( $robots ) && in_array( 'noindex', $robots, true );
		}

		if ( ! $noindex ) {
			$noindex = ( 'yes' === get_post_meta( $post_id, '_seopress_robots_index', true ) );
		}

		return (bool) apply_filters( 'waw_sitemap_is_noindex', $noindex, $post_id );
	}
}
