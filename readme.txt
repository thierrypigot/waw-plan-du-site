=== WAW Plan du site ===
Contributors: thierrypigot
Tags: sitemap, plan du site, html sitemap, accessibilité, shortcode
Requires at least: 6.7
Tested up to: 7.0
Requires PHP: 8.1
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Plan de site HTML accessible via le shortcode [waw_sitemap_page] : filtrage par type de contenu ou taxonomie, tri, profondeur, exclusions.

== Description ==

WAW Plan du site affiche un plan de site HTML sémantique et accessible (RGAA) sur n'importe quelle page, via un simple shortcode.

* Filtrage par types de contenus et/ou taxonomies (`only="page,post,category"`)
* Tri (`sort`, `order`), profondeur de hiérarchie (`sublevel`), exclusions (`exclude`)
* Titres de sections paramétrables (`display_title`, `title_level`)
* Exclusion automatique des contenus noindex (Yoast SEO, Rank Math, SEOPress), protégés par mot de passe, et de la page courante
* Markup accessible : landmark nav, hiérarchie de titres sans saut, aucun style imposé
* 12 filtres pour tout personnaliser (voir HOOKS.md)

La documentation complète est disponible dans Réglages > Plan du site.

== Installation ==

1. Téléversez le dossier `waw-plan-du-site` dans `/wp-content/plugins/`.
2. Activez l'extension.
3. Ajoutez `[waw_sitemap_page]` dans une page.

== Frequently Asked Questions ==

= Comment afficher plusieurs types de contenus ? =

`[waw_sitemap_page only="page,post"]` affiche une section par type.

= Comment limiter la profondeur ? =

`[waw_sitemap_page only="page" sublevel="2"]` limite à deux niveaux (mère et fille).

= Le plugin ajoute-t-il du CSS ? =

Non. Le markup est sémantique (classes BEM `waw-sitemap__*`) et le thème garde la main sur les styles.

== Changelog ==

= 1.0.0 =
* Version initiale : shortcode [waw_sitemap_page], page de documentation dans Réglages, 12 filtres développeur.
