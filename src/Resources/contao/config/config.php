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
use Contao\System;
use InspiredMinds\ContaoSiblingNavigation\Module\SiblingNavigationEvents;
use InspiredMinds\ContaoSiblingNavigation\Module\SiblingNavigationNews;

$bundles = System::getContainer()->getParameter('kernel.bundles');

/*
 * Front end modules
 */
if (\in_array(ContaoNewsBundle::class, $bundles, true)) {
    $GLOBALS['FE_MOD']['news']['sibling_navigation_news'] = SiblingNavigationNews::class;
}

if (\in_array(ContaoCalendarBundle::class, $bundles, true)) {
    $GLOBALS['FE_MOD']['events']['sibling_navigation_events'] = SiblingNavigationEvents::class;
}
