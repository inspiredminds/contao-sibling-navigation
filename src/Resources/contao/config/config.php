<?php

declare(strict_types=1);

/*
 * This file is part of the ContaoSiblingNavigationBundle.
 *
 * (c) inspiredminds
 *
 * @license LGPL-3.0-or-later
 */

use Contao\CalendarBundle\ContaoCalendarBundle;
use Contao\NewsBundle\ContaoNewsBundle;
use InspiredMinds\ContaoSiblingNavigation\Module\SiblingNavigationEvents;
use InspiredMinds\ContaoSiblingNavigation\Module\SiblingNavigationNews;

/*
 * Front end modules
 */
if (class_exists(ContaoNewsBundle::class)) {
    $GLOBALS['FE_MOD']['news']['sibling_navigation_news'] = SiblingNavigationNews::class;
}

if (class_exists(ContaoCalendarBundle::class)) {
    $GLOBALS['FE_MOD']['events']['sibling_navigation_events'] = SiblingNavigationEvents::class;
}
