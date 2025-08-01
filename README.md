# Layout Tools

This is a dependency of the [UMD Libraries Design System theme](https://github.com/umd-lib/umd-design-system-theme/tree/libraries/main).

It has no utility outside of this context.

Use it to extend layout functionality and configurations for Library DS sites.

## Installation

Clone this repo into your themes or install with composer:

```bash
composer require umd-lib/umdlib-design-system-theme
```

## Dependencies

This module depends on the following:

* [Layout Builder Lock](https://www.drupal.org/project/layout_builder_lock)
* [Block List Override](https://www.drupal.org/project/block_list_override)
* [Layout Builder Restrictions](https://www.drupal.org/project/layout_builder_restrictions)

Install these using composer. For example:

```bash
composer require 'drupal/layout_builder_restrictions:^3.0'
```

Install command is available on the module pages.

## Search Web Components Support

Layout Tools and the UMDLIB DS theme support Decoupled Search API
with  Search Web Components:

* [Decoupled Search API](https://www.drupal.org/project/search_api_decoupled)
* [Search Web Components](https://www.drupal.org/project/search_web_components)