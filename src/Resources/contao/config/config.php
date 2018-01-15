<?php

/**
 * This file is part of the ContaoSiblingNavigation Bundle.
 *
 * (c) inspiredminds <https://github.com/inspiredminds>
 *
 * @package   ContaoSiblingNavigation
 * @author    Fritz Michael Gschwantner <https://github.com/fritzmg>
 * @license   LGPL-3.0+
 * @copyright inspiredminds 2018
 */

use InspiredMinds\ContaoSiblingNavigation\Module\SiblingNavigationEvents;
use InspiredMinds\ContaoSiblingNavigation\Module\SiblingNavigationNews;
use Contao\CalendarBundle\ContaoCalendarBundle;
use Contao\NewsBundle\ContaoNewsBundle;
use Contao\System;

$bundles = System::getContainer()->getParameter('kernel.bundles');

/**
 * Front end modules
 */
if (in_array(ContaoNewsBundle::class, $bundles))
{
	$GLOBALS['FE_MOD']['news']['sibling_navigation_news'] = SiblingNavigationNews::class;
}

if (in_array(ContaoCalendarBundle::class, $bundles))
{
	$GLOBALS['FE_MOD']['events']['sibling_navigation_events'] = SiblingNavigationEvents::class;
}
