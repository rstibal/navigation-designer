# Navigation Designer

A WordPress plugin that adds per-instance style controls to the `core/navigation` block, exposed in the block's own Inspector Controls sidebar (Editor → select a Navigation block → "Navigation Designer" panel) — not a separate admin screen.

Three style groups, each with independent desktop and mobile tiers:

- **Navigation** — the block wrapper and top-level menu items (border, corner radius, shadow, gap, item padding, hover/focus color, typography). Base background/text color stays on the block's native Styles > Color controls.
- **Submenu** — the dropdown panel shown for items with children (background, text, border, radius, shadow, padding, offset, typography). Core has no native styling for this at all.
- **Submenu items** — the links inside the dropdown (color incl. hover/focus, padding, typography).

Overrides are compiled to a single scoped CSS file written to `wp-content/uploads/navigation-designer/nav-designer.css` and enqueued after the theme's global styles — not inline styles, not theme.json — so they win on specificity without `!important`-fighting the whole page. The file regenerates automatically whenever a navigation menu, template, or template part is saved.

## Installation

Download the latest release zip from the [Releases](../../releases) page and upload it via Plugins → Add New → Upload Plugin, or extract it into `wp-content/plugins/`.

## Requirements

- WordPress 6.5+
- PHP 7.4+

## Development

```
npm install
npm run start   # watch mode
npm run build   # production build into build/
```

## Releasing

Push a `vMAJOR.MINOR.PATCH` tag matching the `Version:` header in `navigation-designer.php` and the `version` in `package.json`. The `Release` GitHub Actions workflow builds the plugin, packages it into a zip with correct forward-slash paths, and publishes it as a GitHub Release.
