<?php

declare(strict_types=1);

/*
 * This file is part of the ContaoSiblingNavigationBundle.
 *
 * (c) inspiredminds
 *
 * @license LGPL-3.0-or-later
 */

namespace InspiredMinds\ContaoSiblingNavigation\ContaoManager;

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use InspiredMinds\ContaoSiblingNavigation\ContaoSiblingNavigationBundle;

/**
 * Plugin for the Contao Manager.
 */
class Plugin implements BundlePluginInterface
{
    /**
     * {@inheritdoc}
     */
    public function getBundles(ParserInterface $parser)
    {
        return [
            BundleConfig::create(ContaoSiblingNavigationBundle::class)
                ->setLoadAfter([ContaoCoreBundle::class, 'news_sorting']),
        ];
    }
}
