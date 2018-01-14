[![](https://img.shields.io/maintenance/yes/2018.svg)](https://github.com/inspiredminds/contao-sibling-navigation)
[![](https://img.shields.io/packagist/v/inspiredminds/contao-sibling-navigation.svg)](https://packagist.org/packages/inspiredminds/contao-sibling-navigation)
[![](https://img.shields.io/packagist/dt/inspiredminds/contao-sibling-navigation.svg)](https://packagist.org/packages/inspiredminds/contao-sibling-navigation)

Contao Sibling Navigation
=====================

Provides a previous/next navigation for news & events.

## Installation

Require the bundle via composer:
```
composer require inspiredminds/contao-sibling-navigation
```
If you use the Contao Standard Edition, you will have to add
```php
new ContaoSiblingNavigation\ContaoSiblingNavigationBundle()
```
to your `AppKernel.php`.

## Usage

Simply create a _News sibling navigation_ or _Event sibling navigation_ module and integrate it in your page layout or as an include element.

## News Categories features

The sibling navigation can be further limited by providing one or more news category IDs via a GET parameter called `category`. Example:
```
http://example.org/news/detail/foo.html?category=1,3
```
This will limit the previous and next links to news items that have categories with the ID `1` __and__ `3` assigned.
