# Dara: Real Estate WordPress Theme

Fast bilingual (Arabic RTL / English LTR) real estate theme for brokers, agencies and developers. It needs the **Dara Core** plugin (`../dara-core/`), which adds properties, projects, agents, search, leads, blocks and the demo importer.

The full user guide is in [`../documentation/index.html`](../documentation/index.html).

## Development

| Path | Purpose |
| --- | --- |
| `assets/css/src/main.css` | Single CSS source. Edit this file. |
| `build-css.py` | Splits the source into per-template bundles (`core`, `home`, `listing`, `property`, `project`, `content`, `agents`). |
| `build.sh` | Runs the split, then minifies CSS (clean-css) and JS (terser). |
| `inc/` | Setup, Customizer, assets, performance, template tags and the plugin installer. |
| `template-parts/` | Cards, home sections (also used by the blocks) and property parts. |

```sh
npm install        # clean-css-cli + terser
sh build.sh
```

Define `SCRIPT_DEBUG` to load the unminified files.

## Release

From the repository root, run `sh bin/package.sh`. It builds `dist/` with the following:

- `dara.zip`, with Dara Core bundled in `plugins/`.
- `dara-child.zip`.
- `dara-core.zip`.
- `dara-package.zip`, which also includes the documentation and licensing.

Development files (`build.sh`, `build-css.py`, `package.json`, `assets/css/src`, this README) are excluded from the zips.
