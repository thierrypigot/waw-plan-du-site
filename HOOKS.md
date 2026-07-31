# Hooks de waw-plan-du-site

Douze filtres couvrent tout le pipeline de rendu. Ils s'utilisent depuis un
thème (functions.php) ou un mu-plugin.

## Attributs

### `waw_sitemap_default_atts`

Modifie les valeurs par défaut du shortcode, avant parsing.

```php
apply_filters( 'waw_sitemap_default_atts', array $defaults ): array
```

```php
add_filter( 'waw_sitemap_default_atts', function ( array $defaults ): array {
	$defaults['sort'] = 'menu_order';
	return $defaults;
} );
```

### `waw_sitemap_atts`

Modifie les attributs après normalisation (types garantis, whitelist appliquée).
`$raw` contient les attributs bruts du shortcode.

```php
apply_filters( 'waw_sitemap_atts', array $atts, array $raw ): array
```

```php
add_filter( 'waw_sitemap_atts', function ( array $atts ): array {
	$atts['nav_label'] = 'Toutes nos pages';
	return $atts;
} );
```

## Requêtes

### `waw_sitemap_query_args`

Modifie les arguments `WP_Query` d'une section de contenus. `$context` vaut le
nom du post type (section de type) ou `"taxonomie:slug-du-terme"` (contenus
listés sous un terme).

```php
apply_filters( 'waw_sitemap_query_args', array $args, string $context, array $atts ): array
```

```php
add_filter( 'waw_sitemap_query_args', function ( array $args, string $context ): array {
	if ( 'page' === $context ) {
		$args['post__not_in'][] = (int) get_option( 'page_on_front' );
	}
	return $args;
}, 10, 2 );
```

### `waw_sitemap_terms_args`

Modifie les arguments `get_terms()` d'une section taxonomie.

```php
apply_filters( 'waw_sitemap_terms_args', array $args, string $taxonomy, array $atts ): array
```

```php
add_filter( 'waw_sitemap_terms_args', function ( array $args ): array {
	$args['exclude'] = array( 1 ); // Masquer « Non classé ».
	return $args;
} );
```

### `waw_sitemap_posts`

Modifie la liste de posts d'une section après requête et filtrage noindex.
Même convention de `$context` que `waw_sitemap_query_args`.

```php
apply_filters( 'waw_sitemap_posts', WP_Post[] $posts, string $context, array $atts ): array
```

```php
add_filter( 'waw_sitemap_posts', function ( array $posts ): array {
	return array_filter( $posts, fn( WP_Post $p ): bool => 'en-construction' !== $p->post_name );
} );
```

## Exclusions

### `waw_sitemap_excluded_ids`

Modifie la liste des IDs exclus (attribut `exclude` + page courante).

```php
apply_filters( 'waw_sitemap_excluded_ids', int[] $ids, array $atts ): array
```

```php
add_filter( 'waw_sitemap_excluded_ids', function ( array $ids ): array {
	$ids[] = 42;
	return $ids;
} );
```

### `waw_sitemap_is_noindex`

Surcharge la détection noindex (Yoast, Rank Math, SEOPress) pour un contenu.

```php
apply_filters( 'waw_sitemap_is_noindex', bool $noindex, int $post_id ): bool
```

```php
add_filter( 'waw_sitemap_is_noindex', function ( bool $noindex, int $post_id ): bool {
	return $noindex || (bool) get_post_meta( $post_id, '_mon_flag_prive', true );
}, 10, 2 );
```

## HTML

### `waw_sitemap_section_title`

Modifie le titre d'une section (texte brut, échappé ensuite). `$key` est le
nom du post type ou de la taxonomie.

```php
apply_filters( 'waw_sitemap_section_title', string $title, string $key, array $atts ): string
```

```php
add_filter( 'waw_sitemap_section_title', function ( string $title, string $key ): string {
	return 'page' === $key ? 'Nos pages' : $title;
}, 10, 2 );
```

### `waw_sitemap_item`

Modifie le HTML d'un item (le `<li>` complet, sous-liste incluse).

```php
apply_filters( 'waw_sitemap_item', string $html, WP_Post $post, array $atts ): string
```

```php
add_filter( 'waw_sitemap_item', function ( string $html, WP_Post $post ): string {
	if ( 'contact' === $post->post_name ) {
		return str_replace( '</a>', ' ★</a>', $html );
	}
	return $html;
}, 10, 2 );
```

### `waw_sitemap_term_title`

Modifie le HTML du titre d'un terme (le `<hN>` complet, lien inclus).

```php
apply_filters( 'waw_sitemap_term_title', string $html, WP_Term $term, array $atts ): string
```

```php
add_filter( 'waw_sitemap_term_title', function ( string $html, WP_Term $term ): string {
	return $html . sprintf( '<p class="term-desc">%s</p>', esc_html( $term->description ) );
}, 10, 2 );
```

### `waw_sitemap_section_html`

Modifie le HTML complet d'une `<section>`. `$key` est le nom du post type ou
de la taxonomie. Non appelé pour une section vide (jamais rendue).

```php
apply_filters( 'waw_sitemap_section_html', string $html, string $key, array $atts ): string
```

### `waw_sitemap_html`

Modifie la sortie finale (le `<nav>` complet). Point d'entrée idéal pour un
cache maison par transient.

```php
apply_filters( 'waw_sitemap_html', string $html, array $atts ): string
```

```php
add_filter( 'waw_sitemap_html', function ( string $html ): string {
	return $html . "\n<!-- généré par waw-plan-du-site -->";
} );
```
