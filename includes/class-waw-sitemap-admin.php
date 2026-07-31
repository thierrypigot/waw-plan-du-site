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

	public static function render_page(): void {
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Plan du site', 'waw-plan-du-site' ); ?></h1>
			<p><?php esc_html_e( 'Affichez un plan de site HTML accessible avec le shortcode ci-dessous. Aucun réglage nécessaire.', 'waw-plan-du-site' ); ?></p>
			<p><code>[waw_sitemap_page]</code></p>

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
