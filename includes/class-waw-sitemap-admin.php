<?php
/**
 * Page Réglages → Plan du site : documentation du shortcode et des hooks.
 */

defined( 'ABSPATH' ) || exit;

class WAW_Sitemap_Admin {

	public static function register(): void {
		add_options_page(
			__( 'Plan du site', 'waw-plan-du-site' ),
			__( 'Plan du site', 'waw-plan-du-site' ),
			'manage_options',
			'waw-plan-du-site',
			array( __CLASS__, 'render_page' )
		);
	}

	public static function register_settings(): void {
		register_setting(
			'waw_sitemap',
			'waw_sitemap_options',
			array(
				'type'              => 'array',
				'sanitize_callback' => array( __CLASS__, 'sanitize' ),
				'default'           => array(),
			)
		);
	}

	/**
	 * Mêmes whitelists que le renderer : une valeur invalide retombe sur
	 * le défaut codé.
	 */
	public static function sanitize( $input ): array {
		$input = is_array( $input ) ? $input : array();
		$clean = array();

		$clean['sort']  = in_array( $input['sort'] ?? '', array( 'post_title', 'menu_order', 'post_name', 'post_date', 'ID' ), true )
			? $input['sort']
			: 'post_title';
		$clean['order'] = ( 'DESC' === ( $input['order'] ?? '' ) ) ? 'DESC' : 'ASC';

		$clean['title_level'] = in_array( $input['title_level'] ?? '', array( 'h2', 'h3', 'h4' ), true )
			? $input['title_level']
			: 'h2';

		$clean['sublevel']             = max( 0, (int) ( $input['sublevel'] ?? 0 ) );
		$clean['display_title']        = ! empty( $input['display_title'] );
		$clean['sort_ignore_articles'] = ! empty( $input['sort_ignore_articles'] );
		$clean['cache_enabled']        = ! empty( $input['cache_enabled'] );
		$clean['nav_label']            = sanitize_text_field( $input['nav_label'] ?? '' );
		$clean['exclude']              = implode( ',', wp_parse_id_list( $input['exclude'] ?? '' ) );

		return $clean;
	}

	private static function render_settings_form(): void {
		$defaults = WAW_Sitemap_Renderer::defaults();
		?>
		<h2><?php esc_html_e( 'Réglages par défaut', 'waw-plan-du-site' ); ?></h2>
		<p><?php esc_html_e( 'Ces valeurs s\'appliquent à tous les shortcodes qui ne précisent pas l\'attribut correspondant. Un attribut posé dans le shortcode a toujours le dernier mot.', 'waw-plan-du-site' ); ?></p>
		<form method="post" action="options.php">
			<?php settings_fields( 'waw_sitemap' ); ?>
			<table class="form-table" role="presentation">
				<tr>
					<th scope="row"><label for="waw-title-level"><?php esc_html_e( 'Niveau des titres de section', 'waw-plan-du-site' ); ?></label></th>
					<td>
						<select id="waw-title-level" name="waw_sitemap_options[title_level]">
							<?php foreach ( array( 'h2', 'h3', 'h4' ) as $level ) : ?>
								<option value="<?php echo esc_attr( $level ); ?>" <?php selected( $defaults['title_level'], $level ); ?>><?php echo esc_html( $level ); ?></option>
							<?php endforeach; ?>
						</select>
						<p class="description"><?php esc_html_e( 'Les termes de taxonomie prennent automatiquement le niveau suivant.', 'waw-plan-du-site' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="waw-sort"><?php esc_html_e( 'Tri', 'waw-plan-du-site' ); ?></label></th>
					<td>
						<select id="waw-sort" name="waw_sitemap_options[sort]">
							<?php foreach ( array( 'post_title', 'menu_order', 'post_name', 'post_date', 'ID' ) as $sort ) : ?>
								<option value="<?php echo esc_attr( $sort ); ?>" <?php selected( $defaults['sort'], $sort ); ?>><?php echo esc_html( $sort ); ?></option>
							<?php endforeach; ?>
						</select>
						<select name="waw_sitemap_options[order]" aria-label="<?php esc_attr_e( 'Sens du tri', 'waw-plan-du-site' ); ?>">
							<option value="ASC" <?php selected( $defaults['order'], 'ASC' ); ?>><?php esc_html_e( 'Croissant (ASC)', 'waw-plan-du-site' ); ?></option>
							<option value="DESC" <?php selected( $defaults['order'], 'DESC' ); ?>><?php esc_html_e( 'Décroissant (DESC)', 'waw-plan-du-site' ); ?></option>
						</select>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Tri des titres', 'waw-plan-du-site' ); ?></th>
					<td>
						<label for="waw-ignore-articles">
							<input type="checkbox" id="waw-ignore-articles" name="waw_sitemap_options[sort_ignore_articles]" value="1" <?php checked( $defaults['sort_ignore_articles'] ); ?> />
							<?php esc_html_e( 'Ignorer les articles initiaux (« La Bolivie » se classe à B)', 'waw-plan-du-site' ); ?>
						</label>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Titres de section', 'waw-plan-du-site' ); ?></th>
					<td>
						<label for="waw-display-title">
							<input type="checkbox" id="waw-display-title" name="waw_sitemap_options[display_title]" value="1" <?php checked( $defaults['display_title'] ); ?> />
							<?php esc_html_e( 'Afficher un titre au-dessus de chaque section', 'waw-plan-du-site' ); ?>
						</label>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="waw-sublevel"><?php esc_html_e( 'Profondeur maximale', 'waw-plan-du-site' ); ?></label></th>
					<td>
						<input type="number" id="waw-sublevel" name="waw_sitemap_options[sublevel]" value="<?php echo esc_attr( (string) $defaults['sublevel'] ); ?>" min="0" step="1" class="small-text" />
						<p class="description"><?php esc_html_e( '0 = illimité.', 'waw-plan-du-site' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="waw-nav-label"><?php esc_html_e( 'Nom accessible de la navigation', 'waw-plan-du-site' ); ?></label></th>
					<td><input type="text" id="waw-nav-label" name="waw_sitemap_options[nav_label]" value="<?php echo esc_attr( $defaults['nav_label'] ); ?>" class="regular-text" /></td>
				</tr>
				<tr>
					<th scope="row"><label for="waw-exclude"><?php esc_html_e( 'Exclusions globales (IDs)', 'waw-plan-du-site' ); ?></label></th>
					<td>
						<input type="text" id="waw-exclude" name="waw_sitemap_options[exclude]" value="<?php echo esc_attr( implode( ',', (array) $defaults['exclude'] ) ); ?>" class="regular-text" />
						<p class="description"><?php esc_html_e( 'IDs séparés par des virgules, exclus de tous les plans de site.', 'waw-plan-du-site' ); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><?php esc_html_e( 'Cache', 'waw-plan-du-site' ); ?></th>
					<td>
						<label for="waw-cache">
							<input type="checkbox" id="waw-cache" name="waw_sitemap_options[cache_enabled]" value="1" <?php checked( $defaults['cache_enabled'] ); ?> />
							<?php esc_html_e( 'Mettre le plan de site en cache (12 h, invalidé à chaque modification de contenu). Utile au-delà de quelques centaines de pages.', 'waw-plan-du-site' ); ?>
						</label>
					</td>
				</tr>
			</table>
			<?php submit_button( __( 'Enregistrer les réglages', 'waw-plan-du-site' ) ); ?>
		</form>
		<hr />
		<?php
	}

	public static function render_page(): void {
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Plan du site', 'waw-plan-du-site' ); ?></h1>
			<p><?php esc_html_e( 'Affichez un plan de site HTML accessible avec le shortcode ci-dessous.', 'waw-plan-du-site' ); ?></p>
			<p><code>[waw_sitemap_page]</code></p>

			<?php self::render_settings_form(); ?>

			<h2><?php esc_html_e( 'Attributs', 'waw-plan-du-site' ); ?></h2>
			<table class="widefat striped" style="max-width:960px;">
				<thead>
					<tr>
						<th scope="col"><?php esc_html_e( 'Attribut', 'waw-plan-du-site' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Défaut', 'waw-plan-du-site' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Description', 'waw-plan-du-site' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td><code>only</code></td>
						<td><code>page</code></td>
						<td><?php esc_html_e( 'Types de contenus et/ou taxonomies à afficher, séparés par des virgules. Chaque valeur produit une section. Une taxonomie affiche ses termes avec leurs contenus.', 'waw-plan-du-site' ); ?></td>
					</tr>
					<tr>
						<td><code>sort</code></td>
						<td><code>post_title</code></td>
						<td><?php esc_html_e( 'Ordre de tri : post_title, menu_order, post_name, post_date ou ID.', 'waw-plan-du-site' ); ?></td>
					</tr>
					<tr>
						<td><code>order</code></td>
						<td><code>ASC</code></td>
						<td><?php esc_html_e( 'Sens du tri : ASC ou DESC.', 'waw-plan-du-site' ); ?></td>
					</tr>
					<tr>
						<td><code>display_title</code></td>
						<td><code>true</code></td>
						<td><?php esc_html_e( 'Affiche un titre au-dessus de chaque section. Si false, le nom reste porté par un aria-label (accessibilité).', 'waw-plan-du-site' ); ?></td>
					</tr>
					<tr>
						<td><code>title_level</code></td>
						<td><code>h2</code></td>
						<td><?php esc_html_e( 'Niveau du titre de section : h2, h3 ou h4. Les termes de taxonomie prennent le niveau suivant.', 'waw-plan-du-site' ); ?></td>
					</tr>
					<tr>
						<td><code>sublevel</code></td>
						<td><code>0</code></td>
						<td><?php esc_html_e( 'Profondeur maximale de la hiérarchie. 0 = illimité, 2 = deux niveaux (mère et fille).', 'waw-plan-du-site' ); ?></td>
					</tr>
					<tr>
						<td><code>exclude</code></td>
						<td>&mdash;</td>
						<td><?php esc_html_e( 'IDs à exclure, séparés par des virgules. La page qui contient le shortcode est toujours exclue automatiquement.', 'waw-plan-du-site' ); ?></td>
					</tr>
					<tr>
						<td><code>taxonomy</code> + <code>term</code></td>
						<td>&mdash;</td>
						<td><?php esc_html_e( 'Limite les sections de contenus aux éléments du terme donné (slug) dans la taxonomie donnée.', 'waw-plan-du-site' ); ?></td>
					</tr>
					<tr>
						<td><code>exclude_tree</code></td>
						<td>&mdash;</td>
						<td><?php esc_html_e( 'IDs à exclure avec toute leur descendance (contrairement à exclude, où les enfants remontent d\'un niveau).', 'waw-plan-du-site' ); ?></td>
					</tr>
					<tr>
						<td><code>sort_ignore_articles</code></td>
						<td><code>false</code></td>
						<td><?php esc_html_e( 'Ignore les articles initiaux dans le tri alphabétique : « La Bolivie » se classe à B, « L\'Inde » à I.', 'waw-plan-du-site' ); ?></td>
					</tr>
					<tr>
						<td><code>meta_key</code> + <code>meta_value</code></td>
						<td>&mdash;</td>
						<td><?php esc_html_e( 'Limite aux contenus dont la meta vaut la valeur donnée. Avec meta_key seul, la meta doit simplement exister. Exemple champ ACF vrai/faux : meta_key="afficher_sur_la_carte" meta_value="1".', 'waw-plan-du-site' ); ?></td>
					</tr>
					<tr>
						<td><code>nav_label</code></td>
						<td><?php esc_html_e( 'Plan du site', 'waw-plan-du-site' ); ?></td>
						<td><?php esc_html_e( 'Nom accessible du bloc de navigation. À personnaliser si plusieurs plans de site figurent sur la même page, pour que chaque repère reste identifiable au lecteur d\'écran.', 'waw-plan-du-site' ); ?></td>
					</tr>
				</tbody>
			</table>

			<h2><?php esc_html_e( 'Exemples', 'waw-plan-du-site' ); ?></h2>
			<ul>
				<li><code>[waw_sitemap_page only="page"]</code></li>
				<li><code>[waw_sitemap_page only="page" sort="menu_order"]</code></li>
				<li><code>[waw_sitemap_page only="page" sublevel="2"]</code></li>
				<li><code>[waw_sitemap_page only="category"]</code></li>
				<li><code>[waw_sitemap_page only="page,post" exclude="12,34"]</code></li>
				<li><code>[waw_sitemap_page only="post" taxonomy="category" term="actualites"]</code></li>
			</ul>

			<h2><?php esc_html_e( 'Règles automatiques', 'waw-plan-du-site' ); ?></h2>
			<ul style="list-style:disc;padding-left:20px;">
				<li><?php esc_html_e( 'Seuls les contenus publiés sont listés.', 'waw-plan-du-site' ); ?></li>
				<li><?php esc_html_e( 'Les contenus protégés par mot de passe sont exclus.', 'waw-plan-du-site' ); ?></li>
				<li><?php esc_html_e( 'Les contenus marqués noindex (Yoast SEO, Rank Math, SEOPress) sont exclus.', 'waw-plan-du-site' ); ?></li>
			</ul>

			<h2><?php esc_html_e( 'Développeur', 'waw-plan-du-site' ); ?></h2>
			<p>
				<?php esc_html_e( 'Douze filtres couvrent tout le pipeline (attributs, requêtes, exclusions, HTML). La documentation complète se trouve dans le fichier HOOKS.md du plugin :', 'waw-plan-du-site' ); ?>
				<a href="https://github.com/thierrypigot/waw-plan-du-site/blob/main/HOOKS.md" target="_blank" rel="noopener noreferrer">HOOKS.md<span class="screen-reader-text"> <?php esc_html_e( '(nouvel onglet)', 'waw-plan-du-site' ); ?></span></a>
			</p>
			<p><code>waw_sitemap_default_atts</code> · <code>waw_sitemap_atts</code> · <code>waw_sitemap_query_args</code> · <code>waw_sitemap_terms_args</code> · <code>waw_sitemap_posts</code> · <code>waw_sitemap_excluded_ids</code> · <code>waw_sitemap_is_noindex</code> · <code>waw_sitemap_section_title</code> · <code>waw_sitemap_item</code> · <code>waw_sitemap_term_title</code> · <code>waw_sitemap_section_html</code> · <code>waw_sitemap_html</code></p>
		</div>
		<?php
	}
}
