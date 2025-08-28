# Comet Components WordPress plugin for ACF Flexible Content

This plugin is a WordPress implementation of a selection of Double-E Design's Comet Components library for use as ACF Flexible Content modules.

## Usage

This plugin provides default fields, template parts, CSS, and occasionally JS for their output as per the Comet Components library. Some filter hooks are provided to enable some theme-level customisation without having to override entire templates, remove actions, run duplicate functions at higher priority, etc.

Usage with the Comet Canvas (Classic) theme as a parent theme is also recommended as this provides even more integration with Comet Components for a clean and consistent experience for both users and admins. This plugin has not been tested with themes other than Comet Canvas (Classic) and custom themes based on it.

You might also be interested in using Double-E Design's [ACF Dynamic Preview](https://github.com/doubleedesign/acf-dynamic-preview) plugin, which this plugin is designed to be compatible with.

### Filters

:::warning
Use the `get_*_modules` filters with extreme caution. Renaming or removing fields will remove them and/or their current data from the UI, but will not remove it from the database. The intention of these hooks is primarily to allow themes to _add_ fields and options.
:::

| Filter                                  | Usage                                                                                                                        |
|-----------------------------------------|------------------------------------------------------------------------------------------------------------------------------|
| `comet_acf_get_basic_modules`           | Theme-level customisation of the ACF fields for the basic page layout modules.                                               |
| `comet_acf_get_complex_modules`         | Theme-level customisation of the ACF fields for the page layout modules that contain repeaters with nested flexible modules. |
| `comet_acf_get_nestable_modules`        | Theme-level customisation of the modules that can be used within modules with nested repeaters (e.g. accordions).            |
| `comet_acf_flexible_modules_post_types` | Theme-level customisation of the post types that the provided flexible modules are enabled for.                              |

---

## Developer notes

If you're reading this from GitHub, you're seeing the mirror of the [Comet Components WordPress ACF Plugin package](https://github.com/doubleedesign/comet-components/tree/master/packages/comet-plugin) that is here for the purposes of publishing to Packagist and installing via Composer.

Development of this project belongs in the main Comet Components monorepo.

### Local development

When working on this plugin within the monorepo, use `composer.local.json` so that symlinked local packages are used:

```powershell
$env:COMPOSER = "composer.local.json"; composer update --prefer-source
```
