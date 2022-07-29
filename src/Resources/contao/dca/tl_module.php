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
use Contao\CoreBundle\DataContainer\PaletteManipulator;
use Contao\DataContainer;
use Contao\ModuleModel;
use Contao\NewsBundle\ContaoNewsBundle;
use Contao\System;

$container = System::getContainer();
$bundles = $container->getParameter('kernel.bundles');

$GLOBALS['TL_DCA']['tl_module']['config']['onload_callback'][] = function (DataContainer $dc): void {
    if ($dc->id) {
        $module = ModuleModel::findById($dc->id);

        if ('sibling_navigation_news' === $module->type) {
            $GLOBALS['TL_DCA']['tl_module']['fields']['news_archives']['eval']['mandatory'] = false;
        }

        if ('sibling_navigation_events' === $module->type) {
            $GLOBALS['TL_DCA']['tl_module']['fields']['cal_calendar']['eval']['mandatory'] = false;
        }
    }
};

$GLOBALS['TL_DCA']['tl_module']['fields']['siblingShowFirstLast'] = [
    'label' => &$GLOBALS['TL_LANG']['tl_module']['siblingShowFirstLast'],
    'exclude' => true,
    'inputType' => 'checkbox',
    'eval' => ['tl_class' => 'w50 m12'],
    'sql' => "char(1) NOT NULL default ''",
];

/*
 * News sibling navigation
 */
if (\in_array(ContaoNewsBundle::class, $bundles, true)) {
    $GLOBALS['TL_DCA']['tl_module']['palettes']['sibling_navigation_news'] = '
        {title_legend},name,headline,type;
        {config_legend},news_archives,siblingShowFirstLast;
        {template_legend:hide},customTpl;
        {protected_legend:hide},protected;
        {expert_legend:hide},guests,cssID,space;
        {invisible_legend:hide},invisible,start,stop';

    $GLOBALS['TL_DCA']['tl_module']['fields']['news_order']['options_callback'] = function (DataContainer $dc) {
        if ($dc->activeRecord && 'sibling_navigation_news' === $dc->activeRecord->type) {
            return ['order_date_asc', 'order_date_desc', 'order_headline_asc', 'order_headline_desc'];
        }

        return System::importStatic('tl_module_news')->getSortingOptions($dc);
    };

    PaletteManipulator::create()
        ->addField('news_order', 'news_archives', PaletteManipulator::POSITION_AFTER)
        ->applyToPalette('sibling_navigation_news', 'tl_module')
    ;
}

/*
 * Event sibling navigation
 */
if (\in_array(ContaoCalendarBundle::class, $bundles, true)) {
    $GLOBALS['TL_DCA']['tl_module']['palettes']['sibling_navigation_events'] = '
        {title_legend},name,headline,type;
        {config_legend},cal_calendar,siblingShowFirstLast;
        {template_legend:hide},customTpl;
        {protected_legend:hide},protected;
        {expert_legend:hide},guests,cssID,space;
        {invisible_legend:hide},invisible,start,stop';
}
