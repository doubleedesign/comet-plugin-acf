# Comet Components WordPress plugin for ACF Flexible Content

This plugin is a WordPress implementation of a selection of Double-E Design's Comet Components library for use as ACF Flexible Content modules.

This plugin is a foundational requirement for all Double-E Design themes developed for the Classic Editor + ACF from August 2025.

If you're reading this from GitHub, you're seeing the mirror of the [Comet Components WordPress ACF Plugin package](https://github.com/doubleedesign/comet-components/tree/master/packages/comet-plugin) that is here for the purposes of publishing to Packagist and installing via Composer.

Development of this project belongs in the main Comet Components monorepo.

## Developer notes

### Local development

When working on this plugin within the monorepo, use `composer.local.json` so that symlinked local packages are used:

```powershell
$env:COMPOSER = "composer.local.json"; composer update --prefer-source
```
