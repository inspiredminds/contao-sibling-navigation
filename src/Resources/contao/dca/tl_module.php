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

use Contao\CalendarBundle\ContaoCalendarBundle;
use Contao\CoreBundle\DataContainer\PaletteManipulator;
use Contao\DataContainer;
use Contao\NewsBundle\ContaoNewsBundle;
use Contao\System;


$container = System::getContainer();
$bundles = $container->getParameter('kernel.bundles');
$packages = $container->getParameter('kernel.packages');


/**
 * News sibling navigation
 */
if (in_array(ContaoNewsBundle::class, $bundles))
{
    $GLOBALS['TL_DCA']['tl_module']['palettes']['sibling_navigation_news']  = '
        {title_legend},name,headline,type;
        {config_legend},news_archives;
        {protected_legend:hide},protected;
        {expert_legend:hide},guests,cssID,space;
        {invisible_legend:hide},invisible,start,stop';
 
    $version = $packages['contao/news-bundle'];
    if (version_compare($version, '4.5', '>='))
    {
        $GLOBALS['TL_DCA']['tl_module']['fields']['news_order']['options_callback'] = function(DataContainer $dc)
        {
            if ($dc->activeRecord && $dc->activeRecord->type == 'sibling_navigation_news')
            {
                return array('order_date_asc', 'order_date_desc', 'order_headline_asc', 'order_headline_desc');
            }

            return System::importStatic('tl_module_news')->getSortingOptions($dc);
        };

        PaletteManipulator::create()
            ->addField('news_order', 'config_legend', PaletteManipulator::POSITION_APPEND)
            ->applyToPalette('sibling_navigation_news', 'tl_module');
    }
}


/**
 * Event sibling navigation
 */
if (in_array(ContaoCalendarBundle::class, $bundles))
{
    $GLOBALS['TL_DCA']['tl_module']['palettes']['sibling_navigation_events']  = '
        {title_legend},name,headline,type;
        {config_legend},cal_calendar;
        {protected_legend:hide},protected;
        {expert_legend:hide},guests,cssID,space;
        {invisible_legend:hide},invisible,start,stop';
}
