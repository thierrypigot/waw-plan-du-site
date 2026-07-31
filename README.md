# WAW Plan du site

Plugin WordPress maison : plan de site HTML accessible via le shortcode `[waw_sitemap_page]`. Remplace [wp-sitemap-page](https://fr.wordpress.org/plugins/wp-sitemap-page/), qui n'est plus maintenu.

- WordPress 6.7+ · PHP 8.1+ · aucune dépendance, aucun build
- Markup sémantique et accessible (RGAA) : landmark `nav`, hiérarchie de titres sans saut, aucun CSS imposé
- Contenus noindex (Yoast, Rank Math, SEOPress), protégés par mot de passe et page courante exclus automatiquement
- 12 filtres développeur : voir [HOOKS.md](HOOKS.md)

## Utilisation

```text
[waw_sitemap_page only="page"]
[waw_sitemap_page only="page" sort="menu_order"]
[waw_sitemap_page only="page" sort="post_name" order="ASC"]
[waw_sitemap_page only="page" sublevel="2"]
[waw_sitemap_page only="category"]
[waw_sitemap_page only="page,post" exclude="12,34"]
[waw_sitemap_page only="post" taxonomy="category" term="actualites"]
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
| `exclude` | — | IDs à exclure (CSV) |
| `taxonomy` + `term` | — | Limite aux contenus d'un terme |

La documentation est aussi disponible dans l'admin : Réglages > Plan du site.

## Feuille de route

- v2 : bloc Gutenberg dynamique (même moteur de rendu)

## Licence

GPL-2.0-or-later — [WeAre[WP]](https://www.wearewp.pro)
