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
			'only'                 => 'page',
			'sort'                 => 'post_title',
			'order'                => 'ASC',
			'display_title'        => true,
			'title_level'          => 'h2',
			'sublevel'             => 0,
			'exclude'              => array(),
			'exclude_tree'         => array(),
			'taxonomy'             => '',
			'term'                 => '',
			'meta_key'             => '',
			'meta_value'           => '',
			'sort_ignore_articles' => false,
			'cache_enabled'        => false,
			'nav_label'            => __( 'Plan du site', 'waw-plan-du-site' ),
		);

		// Les défauts enregistrés dans Réglages → Plan du site priment sur
		// les défauts codés ; les attributs du shortcode priment sur tout.
		$saved = get_option( 'waw_sitemap_options', array() );

		if ( is_array( $saved ) ) {
			$defaults = array_merge( $defaults, array_intersect_key( $saved, $defaults ) );
		}

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

		$atts['only'] = array_values( array_unique( array_filter( array_map(
			'sanitize_key',
			array_map( 'trim', explode( ',', (string) ( is_array( $atts['only'] ) ? implode( ',', $atts['only'] ) : $atts['only'] ) ) )
		) ) ) );

		$sort         = strtolower( (string) $atts['sort'] );
		$sort         = ( 'id' === $sort ) ? 'ID' : $sort;
		$atts['sort'] = array_key_exists( $sort, self::ORDERBY_MAP ) ? $sort : $defaults['sort'];

		$atts['order'] = ( 'DESC' === strtoupper( (string) $atts['order'] ) ) ? 'DESC' : 'ASC';

		$atts['display_title'] = filter_var( $atts['display_title'], FILTER_VALIDATE_BOOLEAN );

		$level               = strtolower( (string) $atts['title_level'] );
		$atts['title_level'] = in_array( $level, self::TITLE_LEVELS, true ) ? $level : $defaults['title_level'];

		$atts['sublevel']     = max( 0, (int) $atts['sublevel'] );
		$atts['exclude']      = wp_parse_id_list( $atts['exclude'] );
		$atts['exclude_tree'] = wp_parse_id_list( $atts['exclude_tree'] );
		$atts['taxonomy']     = sanitize_key( (string) $atts['taxonomy'] );
		$atts['term']         = sanitize_title( (string) $atts['term'] );

		$atts['meta_key']   = trim( (string) $atts['meta_key'] );
		$atts['meta_value'] = (string) $atts['meta_value'];

		$atts['sort_ignore_articles'] = filter_var( $atts['sort_ignore_articles'], FILTER_VALIDATE_BOOLEAN );
		$atts['cache_enabled']        = filter_var( $atts['cache_enabled'], FILTER_VALIDATE_BOOLEAN );
		// Un landmark sans nom accessible est pire que le défaut : repli si vide.
		$nav_label         = trim( (string) $atts['nav_label'] );
		$atts['nav_label'] = ( '' === $nav_label ) ? (string) $defaults['nav_label'] : $nav_label;

		return (array) apply_filters( 'waw_sitemap_atts', $atts, $raw );
	}

	/**
	 * IDs à exclure : attribut `exclude` + page courante.
	 */
	private static function excluded_ids( array $atts ): array {
		$ids     = array_merge( $atts['exclude'], $atts['exclude_tree'] );
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

	/**
	 * Point d'entrée : rend le plan de site complet, via le cache si activé.
	 *
	 * @param array $raw_atts Attributs bruts (shortcode ou bloc).
	 */
	public static function render( array $raw_atts ): string {
		$atts = self::normalize( $raw_atts );

		if ( ! $atts['cache_enabled'] ) {
			return self::build( $atts );
		}

		$cache_key = WAW_Sitemap_Cache::key( $atts );
		$cached    = get_transient( $cache_key );

		if ( is_array( $cached ) && isset( $cached['html'] ) ) {
			return (string) $cached['html'];
		}

		$html = self::build( $atts );
		set_transient( $cache_key, array( 'html' => $html ), WAW_Sitemap_Cache::ttl() );

		return $html;
	}

	/**
	 * Construit le HTML à partir d'attributs déjà normalisés.
	 */
	private static function build( array $atts ): string {
		$sections = '';
		$debug    = '';

		foreach ( $atts['only'] as $key ) {
			if ( post_type_exists( $key ) ) {
				$sections .= self::render_post_type_section( $key, $atts );
			} elseif ( taxonomy_exists( $key ) ) {
				$sections .= self::render_taxonomy_section( $key, $atts );
			} elseif ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
				$debug .= sprintf(
					"\n<!-- waw-sitemap : la valeur only=\"%s\" ne correspond ni à un type de contenu ni à une taxonomie. -->\n",
					esc_html( $key )
				);
			}
		}

		// Pas de <nav> vide : le landmark n'est rendu qu'avec du contenu réel.
		if ( '' === $sections ) {
			return (string) apply_filters( 'waw_sitemap_html', $debug, $atts );
		}

		$html = sprintf(
			'<nav class="waw-sitemap" aria-label="%s">%s</nav>',
			esc_attr( $atts['nav_label'] ),
			$sections
		) . $debug;

		return (string) apply_filters( 'waw_sitemap_html', $html, $atts );
	}

	/**
	 * Contenus publiés d'un type, triés, hors exclusions/protégés/noindex.
	 *
	 * @return WP_Post[]
	 */
	private static function get_section_posts( string $post_type, array $atts ): array {
		$args = array(
			'post_type'              => $post_type,
			'post_status'            => 'publish',
			'posts_per_page'         => -1,
			'orderby'                => self::ORDERBY_MAP[ $atts['sort'] ],
			'order'                  => $atts['order'],
			'post__not_in'           => self::excluded_ids( $atts ),
			'has_password'           => false,
			'no_found_rows'          => true,
			'update_post_term_cache' => false,
			'ignore_custom_sort'     => true,
			'suppress_filters'       => false,
		);

		if ( '' !== $atts['taxonomy'] && '' !== $atts['term'] && taxonomy_exists( $atts['taxonomy'] ) ) {
			$args['tax_query'] = array(
				array(
					'taxonomy' => $atts['taxonomy'],
					'field'    => 'slug',
					'terms'    => $atts['term'],
				),
			);
		}

		$args = self::maybe_add_meta_filter( $args, $atts );
		$args = (array) apply_filters( 'waw_sitemap_query_args', $args, $post_type, $atts );

		// get_posts() amorce le cache des metas : les get_post_meta() de
		// is_noindex() ne déclenchent aucune requête supplémentaire.
		$posts = get_posts( $args );
		$posts = array_values( array_filter( $posts, fn( $p ) => ! self::is_noindex( $p->ID ) ) );
		$posts = self::apply_exclude_tree( $posts, $atts );
		$posts = self::sort_posts( $posts, $atts );

		return (array) apply_filters( 'waw_sitemap_posts', $posts, $post_type, $atts );
	}

	/**
	 * Ré-applique le tri demandé en PHP : les plugins de tri personnalisé
	 * (Post Types Order et similaires) écrasent l'orderby SQL via
	 * pre_get_posts, ce qui casserait le contrat de l'attribut `sort`.
	 * Le tri des titres ignore les accents (Éthiopie se classe à E).
	 *
	 * @param WP_Post[] $posts
	 * @return WP_Post[]
	 */
	private static function sort_posts( array $posts, array $atts ): array {
		$key     = $atts['sort'];
		$order   = ( 'DESC' === $atts['order'] ) ? -1 : 1;
		$ignore  = $atts['sort_ignore_articles'];

		$by_title = static fn( WP_Post $a, WP_Post $b ): int => strcmp(
			self::title_sort_key( $a->post_title, $ignore ),
			self::title_sort_key( $b->post_title, $ignore )
		);

		usort( $posts, static function ( WP_Post $a, WP_Post $b ) use ( $key, $order, $by_title ): int {
			$cmp = match ( $key ) {
				'menu_order' => $a->menu_order <=> $b->menu_order,
				'post_date'  => strcmp( $a->post_date, $b->post_date ),
				'post_name'  => strcmp( $a->post_name, $b->post_name ),
				'ID'         => $a->ID <=> $b->ID,
				default      => $by_title( $a, $b ),
			};

			if ( 0 === $cmp && 'post_title' !== $key ) {
				$cmp = $by_title( $a, $b );
			}

			return $cmp * $order;
		} );

		return $posts;
	}

	/**
	 * Clé de tri d'un titre : accents ignorés et, sur demande, articles
	 * initiaux français ignorés (« La Bolivie » se classe à B).
	 */
	private static function title_sort_key( string $title, bool $ignore_articles ): string {
		$key = strtolower( remove_accents( $title ) );

		if ( $ignore_articles ) {
			$key = (string) preg_replace( "/^(?:les?|la|une?|des|du)\s+|^(?:l|d)['’]\s*/u", '', $key );
		}

		return trim( $key );
	}

	/**
	 * Retire les descendants des contenus listés dans `exclude_tree`
	 * (les racines sont déjà exclues de la requête).
	 *
	 * @param WP_Post[] $posts
	 * @return WP_Post[]
	 */
	private static function apply_exclude_tree( array $posts, array $atts ): array {
		if ( array() === $atts['exclude_tree'] ) {
			return $posts;
		}

		return array_values( array_filter(
			$posts,
			fn( $p ) => array() === array_intersect( $atts['exclude_tree'], get_post_ancestors( $p ) )
		) );
	}

	/**
	 * Filtre par meta : égalité si `meta_value` est fourni, sinon simple
	 * existence de la clé. Exemple ACF true/false : meta_value="1".
	 */
	private static function maybe_add_meta_filter( array $args, array $atts ): array {
		if ( '' === $atts['meta_key'] ) {
			return $args;
		}

		$args['meta_query'] = array(
			( '' === $atts['meta_value'] )
				? array(
					'key'     => $atts['meta_key'],
					'compare' => 'EXISTS',
				)
				: array(
					'key'   => $atts['meta_key'],
					'value' => $atts['meta_value'],
				),
		);

		return $args;
	}

	/**
	 * Regroupe les posts par parent. Un post dont le parent est absent de la
	 * liste (exclu, non publié…) est rattaché à son plus proche ancêtre
	 * visible, ou à la racine.
	 *
	 * @param WP_Post[] $posts
	 * @return array<int, WP_Post[]> Map parent_id → enfants (ordre de requête préservé).
	 */
	private static function build_tree( array $posts ): array {
		$visible = array_flip( wp_list_pluck( $posts, 'ID' ) );
		$tree    = array();

		foreach ( $posts as $post ) {
			$parent = (int) $post->post_parent;

			if ( $parent && ! isset( $visible[ $parent ] ) ) {
				$parent = self::closest_visible_ancestor( $post, $visible );
			}

			$tree[ $parent ][] = $post;
		}

		return $tree;
	}

	private static function closest_visible_ancestor( WP_Post $post, array $visible ): int {
		$parent_id = (int) $post->post_parent;

		while ( $parent_id ) {
			if ( isset( $visible[ $parent_id ] ) ) {
				return $parent_id;
			}
			$parent    = get_post( $parent_id );
			$parent_id = $parent ? (int) $parent->post_parent : 0;
		}

		return 0;
	}

	/**
	 * Rend récursivement un <ul> à partir de l'arbre, borné par `sublevel`.
	 */
	private static function render_list( array $tree, int $parent, int $depth, array $atts ): string {
		if ( empty( $tree[ $parent ] ) ) {
			return '';
		}

		if ( $atts['sublevel'] > 0 && $depth > $atts['sublevel'] ) {
			return '';
		}

		$items = '';

		foreach ( $tree[ $parent ] as $post ) {
			$item = sprintf(
				'<li class="waw-sitemap__item"><a href="%s">%s</a>%s</li>',
				esc_url( get_permalink( $post ) ),
				esc_html( get_the_title( $post ) ),
				self::render_list( $tree, $post->ID, $depth + 1, $atts )
			);

			$items .= (string) apply_filters( 'waw_sitemap_item', $item, $post, $atts );
		}

		return sprintf( '<ul class="waw-sitemap__list">%s</ul>', $items );
	}

	private static function render_post_type_section( string $post_type, array $atts ): string {
		$posts = self::get_section_posts( $post_type, $atts );

		if ( array() === $posts ) {
			return '';
		}

		$pto   = get_post_type_object( $post_type );
		$title = $pto ? $pto->labels->name : $post_type;
		$title = (string) apply_filters( 'waw_sitemap_section_title', $title, $post_type, $atts );

		$list = self::render_list( self::build_tree( $posts ), 0, 1, $atts );
		$html = self::wrap_section( $post_type, $title, $list, $atts );

		return (string) apply_filters( 'waw_sitemap_section_html', $html, $post_type, $atts );
	}

	/**
	 * <section> commun. Si le titre visible est masqué, il devient un
	 * aria-label pour conserver un nom accessible (RGAA).
	 */
	private static function wrap_section( string $key, string $title, string $content, array $atts ): string {
		$heading    = '';
		$label_attr = '';

		if ( $atts['display_title'] ) {
			$heading = sprintf(
				'<%1$s class="waw-sitemap__title">%2$s</%1$s>',
				$atts['title_level'],
				esc_html( $title )
			);
		} else {
			$label_attr = sprintf( ' aria-label="%s"', esc_attr( $title ) );
		}

		return sprintf(
			'<section class="waw-sitemap__section waw-sitemap__section--%s"%s>%s%s</section>',
			esc_attr( $key ),
			$label_attr,
			$heading,
			$content
		);
	}

	private static function render_taxonomy_section( string $taxonomy, array $atts ): string {
		$args = array(
			'taxonomy'   => $taxonomy,
			'hide_empty' => true,
			'orderby'    => 'name',
			'order'      => $atts['order'],
		);

		$args  = (array) apply_filters( 'waw_sitemap_terms_args', $args, $taxonomy, $atts );
		$terms = get_terms( $args );

		if ( is_wp_error( $terms ) || array() === $terms ) {
			return '';
		}

		$tree = array();
		foreach ( $terms as $term ) {
			$tree[ (int) $term->parent ][] = $term;
		}

		$content = self::render_term_group( $tree, 0, 1, $atts );

		if ( '' === $content ) {
			return '';
		}

		$tax   = get_taxonomy( $taxonomy );
		$title = $tax ? $tax->labels->name : $taxonomy;
		$title = (string) apply_filters( 'waw_sitemap_section_title', $title, $taxonomy, $atts );

		$html = self::wrap_section( $taxonomy, $title, $content, $atts );

		return (string) apply_filters( 'waw_sitemap_section_html', $html, $taxonomy, $atts );
	}

	/**
	 * Rend récursivement les termes (bornés par `sublevel`), chacun avec la
	 * liste de ses contenus. Niveau de titre = niveau de section + profondeur,
	 * plafonné à h6.
	 *
	 * @param array<int, WP_Term[]> $tree Map parent_id → termes enfants.
	 */
	private static function render_term_group( array $tree, int $parent, int $depth, array $atts ): string {
		if ( empty( $tree[ $parent ] ) ) {
			return '';
		}

		if ( $atts['sublevel'] > 0 && $depth > $atts['sublevel'] ) {
			return '';
		}

		// Sans titre de section visible, les termes reprennent son niveau
		// pour ne pas créer de saut dans la hiérarchie de titres (RGAA 9.1).
		$offset        = $atts['display_title'] ? $depth : $depth - 1;
		$heading_level = min( 6, max( 2, (int) substr( $atts['title_level'], 1 ) + $offset ) );
		$items         = '';

		foreach ( $tree[ $parent ] as $term ) {
			$posts    = self::get_term_posts( $term, $atts );
			$children = self::render_term_group( $tree, (int) $term->term_id, $depth + 1, $atts );

			if ( array() === $posts && '' === $children ) {
				continue;
			}

			// Sans permalien valide, le nom du terme est rendu sans lien
			// plutôt qu'avec un href vide trompeur (WCAG 2.4.4).
			$term_link = get_term_link( $term );

			if ( is_wp_error( $term_link ) || '' === $term_link ) {
				$title_html = sprintf(
					'<h%1$d class="waw-sitemap__term-title">%2$s</h%1$d>',
					$heading_level,
					esc_html( $term->name )
				);
			} else {
				$title_html = sprintf(
					'<h%1$d class="waw-sitemap__term-title"><a href="%2$s">%3$s</a></h%1$d>',
					$heading_level,
					esc_url( $term_link ),
					esc_html( $term->name )
				);
			}
			$title_html = (string) apply_filters( 'waw_sitemap_term_title', $title_html, $term, $atts );

			$posts_list = ( array() === $posts )
				? ''
				: self::render_list( self::build_tree( $posts ), 0, 1, $atts );

			$items .= sprintf(
				'<li class="waw-sitemap__term">%s%s%s</li>',
				$title_html,
				$posts_list,
				$children
			);
		}

		if ( '' === $items ) {
			return '';
		}

		return sprintf( '<ul class="waw-sitemap__terms">%s</ul>', $items );
	}

	/**
	 * Contenus publiés directement rattachés à un terme (sans les enfants).
	 *
	 * @return WP_Post[]
	 */
	private static function get_term_posts( WP_Term $term, array $atts ): array {
		$tax = get_taxonomy( $term->taxonomy );

		$args = array(
			'post_type'              => $tax ? $tax->object_type : 'post',
			'post_status'            => 'publish',
			'posts_per_page'         => -1,
			'orderby'                => self::ORDERBY_MAP[ $atts['sort'] ],
			'order'                  => $atts['order'],
			'post__not_in'           => self::excluded_ids( $atts ),
			'has_password'           => false,
			'no_found_rows'          => true,
			'update_post_term_cache' => false,
			'ignore_custom_sort'     => true,
			'suppress_filters'       => false,
			'tax_query'              => array(
				array(
					'taxonomy'         => $term->taxonomy,
					'field'            => 'term_id',
					'terms'            => $term->term_id,
					'include_children' => false,
				),
			),
		);

		$args  = self::maybe_add_meta_filter( $args, $atts );
		$args  = (array) apply_filters( 'waw_sitemap_query_args', $args, $term->taxonomy . ':' . $term->slug, $atts );
		$posts = get_posts( $args );
		$posts = array_values( array_filter( $posts, fn( $p ) => ! self::is_noindex( $p->ID ) ) );
		$posts = self::apply_exclude_tree( $posts, $atts );
		$posts = self::sort_posts( $posts, $atts );

		return (array) apply_filters( 'waw_sitemap_posts', $posts, $term->taxonomy . ':' . $term->slug, $atts );
	}
}
