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


/**
 * Palettes
 */
$GLOBALS['TL_DCA']['tl_module']['palettes']['sibling_navigation_news']  = '
    {title_legend},name,headline,type;
    {config_legend},news_archives;
    {protected_legend:hide},protected;
    {expert_legend:hide},guests,cssID,space;
    {invisible_legend:hide},invisible,start,stop';

$GLOBALS['TL_DCA']['tl_module']['palettes']['sibling_navigation_events']  = '
    {title_legend},name,headline,type;
    {config_legend},cal_calendar;
    {protected_legend:hide},protected;
    {expert_legend:hide},guests,cssID,space;
    {invisible_legend:hide},invisible,start,stop';
