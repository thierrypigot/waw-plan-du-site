# WAW Plan du site

Plugin WordPress maison : plan de site HTML accessible via le shortcode `[waw_sitemap_page]`. Remplace [wp-sitemap-page](https://fr.wordpress.org/plugins/wp-sitemap-page/), qui n'est plus maintenu.

- WordPress 6.7+ · PHP 8.1+ · aucun build
- Markup sémantique et accessible (RGAA) : landmark `nav`, hiérarchie de titres sans saut, aucun CSS imposé
- Contenus noindex (Yoast, Rank Math, SEOPress), protégés par mot de passe et page courante exclus automatiquement
- Écran de réglages (Réglages > Plan du site) : valeurs par défaut globales, le shortcode garde le dernier mot
- Mises à jour automatiques depuis les releases GitHub (plugin-update-checker)
- Cache transient optionnel, invalidé à chaque modification de contenu
- Compatible multilingue (WPML/Polylang) et résistant aux plugins de tri type Post Types Order
- 13 filtres développeur : voir [HOOKS.md](HOOKS.md)

## Utilisation

```text
[waw_sitemap_page only="page"]
[waw_sitemap_page only="page" sort="menu_order"]
[waw_sitemap_page only="page" sort="post_name" order="ASC"]
[waw_sitemap_page only="page" sublevel="2"]
[waw_sitemap_page only="category"]
[waw_sitemap_page only="page,post" exclude="12,34"]
[waw_sitemap_page only="post" taxonomy="category" term="actualites"]
[waw_sitemap_page only="projet" meta_key="afficher_sur_la_carte" meta_value="1"]
[waw_sitemap_page display_title="false"]
```

| Attribut | Défaut | Rôle |
|---|---|---|
| `only` | `page` | Types de contenus et/ou taxonomies (CSV). Une taxonomie affiche ses termes avec leurs contenus. |
| `sort` | `post_title` | `post_title`, `menu_order`, `post_name`, `post_date`, `ID` |
| `order` | `ASC` | `ASC` ou `DESC` |
| `display_title` | `true` | Titre au-dessus de chaque section (sinon `aria-label`) |
| `title_level` | `h2` | `h2`, `h3` ou `h4` |
| `sublevel` | `0` | Profondeur max (0 = illimité) |
| `exclude` | — | IDs à exclure (CSV) ; leurs enfants remontent d'un niveau |
| `exclude_tree` | — | IDs à exclure avec toute leur descendance |
| `sort_ignore_articles` | `false` | Ignore les articles initiaux dans le tri (« La Bolivie » à B) |
| `taxonomy` + `term` | — | Limite aux contenus d'un terme |
| `meta_key` + `meta_value` | — | Limite aux contenus dont la meta vaut la valeur (ACF true/false : `meta_value="1"`). `meta_key` seul = la meta doit exister. |
| `nav_label` | `Plan du site` | Nom accessible du landmark `nav`. À personnaliser si plusieurs plans de site sur une même page. |

La documentation est aussi disponible dans l'admin : Réglages > Plan du site.

## Feuille de route

- v2 : bloc Gutenberg dynamique (même moteur de rendu)

## Licence

GPL-2.0-or-later — [WeAre[WP]](https://www.wearewp.pro)
