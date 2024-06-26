<?php

declare(strict_types=1);

/*
 * This file is part of the ContaoSiblingNavigationBundle.
 *
 * (c) inspiredminds
 *
 * @license LGPL-3.0-or-later
 */

namespace InspiredMinds\ContaoSiblingNavigation\Module;

use Codefog\NewsCategoriesBundle\CodefogNewsCategoriesBundle;
use Contao\BackendTemplate;
use Contao\Config;
use Contao\Date;
use Contao\Input;
use Contao\ModuleNews;
use Contao\News;
use Contao\NewsModel;
use Contao\StringUtil;
use Contao\System;

if (class_exists(\Haste\Model\Model::class)) {
    class HasteModel extends \Haste\Model\Model
    {
    }
} else if (class_exists(\Codefog\HasteBundle\Model\DcaRelationsModel::class)){
    class HasteModel extends \Codefog\HasteBundle\Model\DcaRelationsModel
    {
    }
}

class SiblingNavigationNews extends ModuleNews
{
    /**
     * Template.
     *
     * @var string
     */
    protected $strTemplate = 'mod_sibling_navigation';

    /**
     * Current news item.
     *
     * @var NewsModel
     */
    protected $currentNews;

    /**
     * Display a wildcard in the back end.
     *
     * @return string
     */
    public function generate()
    {
        $request = System::getContainer()->get('request_stack')->getCurrentRequest();

        if ($request && System::getContainer()->get('contao.routing.scope_matcher')->isBackendRequest($request)) {
            $objTemplate = new BackendTemplate('be_wildcard');

            $objTemplate->wildcard = '### '.mb_strtoupper($GLOBALS['TL_LANG']['FMD']['sibling_navigation_news'][0]).' ###';

            $objTemplate->title = $this->headline;
            $objTemplate->id = $this->id;
            $objTemplate->link = $this->name;
            $objTemplate->href = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id='.$this->id;

            return $objTemplate->parse();
        }

        // Set the item from the auto_item parameter
        $items = Input::get('items', false, true);
        $autoItem = Input::get('auto_item', false, true);

        if (null === $items && false !== Config::get('useAutoItem') && null !== $autoItem) {
            $item = $autoItem;
        } else {
            $item = $items;
        }

        if (null === $item) {
            return '';
        }

        $this->news_archives = $this->sortOutProtected(StringUtil::deserialize($this->news_archives, true));

        $this->currentNews = NewsModel::findByIdOrAlias($item);

        if (null === $this->currentNews) {
            return '';
        }

        return parent::generate();
    }

    /**
     * Generate the module.
     */
    protected function compile(): void
    {
        // Check if archive of current news item is within the enabled archives
        if (!\in_array($this->currentNews->pid, $this->news_archives, true)) {
            $this->news_archives = [$this->currentNews->pid];
        }

        $t = NewsModel::getTable();

        // Basic query definition
        $arrQuery = [
            'column' => [
                "$t.pid IN(".implode(',', array_map('intval', $this->news_archives)).')',
                "$t.id != ?",
            ],
            'value' => [$this->currentNews->id],
            'limit' => 1,
            'return' => 'Model',
        ];

        if (!$this->isPreviewMode()) {
            $time = Date::floorToMinute();
            $arrQuery['column'][] = "$t.published='1' AND ($t.start='' OR $t.start<='$time') AND ($t.stop='' OR $t.stop>'$time')";
        }

        // Get category parameter
        $strCategory = Input::get('category');

        // Check for category input
        $bundles = System::getContainer()->getParameter('kernel.bundles');
        if ($strCategory && \in_array(CodefogNewsCategoriesBundle::class, $bundles, true)) {
            $arrCategories = StringUtil::trimsplit(',', $strCategory);
            $arrCategoryNewsIds = [];

            // Go through each category
            foreach ($arrCategories as $category) {
                // Get the news items for this category
                $arrNewsIds = HasteModel::getReferenceValues('tl_news', 'categories', $category);

                // Intersect all news IDs (ignoring empty ones)
                if ($arrCategoryNewsIds && $arrNewsIds) {
                    $arrCategoryNewsIds = array_intersect($arrCategoryNewsIds, $arrNewsIds);
                } elseif (!$arrCategoryNewsIds) {
                    $arrCategoryNewsIds = $arrNewsIds;
                }
            }

            $arrCategoryNewsIds = array_map('intval', $arrCategoryNewsIds);
            $arrCategoryNewsIds = array_filter($arrCategoryNewsIds);
            $arrCategoryNewsIds = array_unique($arrCategoryNewsIds);

            if ($arrCategoryNewsIds) {
                $arrQuery['column'][] = "$t.id IN(".implode(',', $arrCategoryNewsIds).')';
            }
        }

        $arrQueryPrev = $arrQuery;
        $arrQueryNext = $arrQuery;
        $arrQueryFirst = $arrQuery;
        $arrQueryLast = $arrQuery;

        // support for news_sorting and news_order
        $this->news_order = $this->news_sorting ?: $this->news_order;
        switch ($this->news_order) {
            case 'sort_date_asc':
            case 'order_date_asc':
                $arrQueryPrev['column'][] = "$t.date > ?";
                $arrQueryPrev['value'][] = $this->currentNews->date;
                $arrQueryPrev['order'] = "$t.date ASC";

                $arrQueryNext['column'][] = "$t.date < ?";
                $arrQueryNext['value'][] = $this->currentNews->date;
                $arrQueryNext['order'] = "$t.date DESC";

                $arrQueryFirst['column'][] = "$t.date > ?";
                $arrQueryFirst['value'][] = $this->currentNews->date;
                $arrQueryFirst['order'] = "$t.date DESC";

                $arrQueryLast['column'][] = "$t.date < ?";
                $arrQueryLast['value'][] = $this->currentNews->date;
                $arrQueryLast['order'] = "$t.date ASC";
                break;

            case 'sort_headline_asc':
            case 'order_headline_asc':
                $arrQueryPrev['column'][] = "$t.headline > ?";
                $arrQueryPrev['value'][] = $this->currentNews->headline;
                $arrQueryPrev['order'] = "$t.headline ASC";

                $arrQueryNext['column'][] = "$t.headline < ?";
                $arrQueryNext['value'][] = $this->currentNews->headline;
                $arrQueryNext['order'] = "$t.headline DESC";

                $arrQueryFirst['column'][] = "$t.headline > ?";
                $arrQueryFirst['value'][] = $this->currentNews->headline;
                $arrQueryFirst['order'] = "$t.headline DESC";

                $arrQueryLast['column'][] = "$t.headline < ?";
                $arrQueryLast['value'][] = $this->currentNews->headline;
                $arrQueryLast['order'] = "$t.headline ASC";
                break;

            case 'sort_headline_desc':
            case 'order_headline_desc':
                $arrQueryPrev['column'][] = "$t.headline < ?";
                $arrQueryPrev['value'][] = $this->currentNews->headline;
                $arrQueryPrev['order'] = "$t.headline DESC";

                $arrQueryNext['column'][] = "$t.headline > ?";
                $arrQueryNext['value'][] = $this->currentNews->headline;
                $arrQueryNext['order'] = "$t.headline ASC";

                $arrQueryFirst['column'][] = "$t.headline < ?";
                $arrQueryFirst['value'][] = $this->currentNews->headline;
                $arrQueryFirst['order'] = "$t.headline ASC";

                $arrQueryLast['column'][] = "$t.headline > ?";
                $arrQueryLast['value'][] = $this->currentNews->headline;
                $arrQueryLast['order'] = "$t.headline DESC";
                break;

            case 'order_custom_date_asc':
                $arrQueryPrev['column'][] = "$t.sorting < ?";
                $arrQueryPrev['value'][] = $this->currentNews->sorting;
                $arrQueryPrev['order'] = "$t.sorting DESC, $t.date ASC";

                $arrQueryNext['column'][] = "$t.sorting > ?";
                $arrQueryNext['value'][] = $this->currentNews->sorting;
                $arrQueryNext['order'] = "$t.sorting ASC, $t.date ASC";

                $arrQueryFirst['column'][] = "$t.sorting < ?";
                $arrQueryFirst['value'][] = $this->currentNews->sorting;
                $arrQueryFirst['order'] = "$t.sorting ASC, $t.date ASC";

                $arrQueryLast['column'][] = "$t.sorting > ?";
                $arrQueryLast['value'][] = $this->currentNews->sorting;
                $arrQueryLast['order'] = "$t.sorting DESC, $t.date ASC";
                break;

            case 'order_custom_date_desc':
                $arrQueryPrev['column'][] = "$t.sorting < ?";
                $arrQueryPrev['value'][] = $this->currentNews->sorting;
                $arrQueryPrev['order'] = "$t.sorting DESC, $t.date DESC";

                $arrQueryNext['column'][] = "$t.sorting > ?";
                $arrQueryNext['value'][] = $this->currentNews->sorting;
                $arrQueryNext['order'] = "$t.sorting ASC, $t.date DESC";

                $arrQueryFirst['column'][] = "$t.sorting < ?";
                $arrQueryFirst['value'][] = $this->currentNews->sorting;
                $arrQueryFirst['order'] = "$t.sorting ASC, $t.date DESC";

                $arrQueryLast['column'][] = "$t.sorting > ?";
                $arrQueryLast['value'][] = $this->currentNews->sorting;
                $arrQueryLast['order'] = "$t.sorting DESC, $t.date DESC";
                break;

            default:
                $arrQueryPrev['column'][] = "$t.date < ?";
                $arrQueryPrev['value'][] = $this->currentNews->date;
                $arrQueryPrev['order'] = "$t.date DESC";

                $arrQueryNext['column'][] = "$t.date > ?";
                $arrQueryNext['value'][] = $this->currentNews->date;
                $arrQueryNext['order'] = "$t.date ASC";

                $arrQueryFirst['column'][] = "$t.date < ?";
                $arrQueryFirst['value'][] = $this->currentNews->date;
                $arrQueryFirst['order'] = "$t.date ASC";

                $arrQueryLast['column'][] = "$t.date > ?";
                $arrQueryLast['value'][] = $this->currentNews->date;
                $arrQueryLast['order'] = "$t.date DESC";
        }

        $objFirst = NewsModel::findAll($arrQueryFirst);
        $objLast = NewsModel::findAll($arrQueryLast);

        $objPrev = NewsModel::findAll($arrQueryPrev);
        $objNext = NewsModel::findAll($arrQueryNext);

        $strFirstLink = $objFirst ? News::generateNewsUrl($objFirst).($strCategory ? '?category='.$strCategory : '') : null;
        $strLastLink = $objLast ? News::generateNewsUrl($objLast).($strCategory ? '?category='.$strCategory : '') : null;

        $strPrevLink = $objPrev ? News::generateNewsUrl($objPrev).($strCategory ? '?category='.$strCategory : '') : null;
        $strNextLink = $objNext ? News::generateNewsUrl($objNext).($strCategory ? '?category='.$strCategory : '') : null;

        $this->Template->first = $strFirstLink;
        $this->Template->last = $strLastLink;
        $this->Template->prev = $strPrevLink;
        $this->Template->next = $strNextLink;
        $this->Template->firstTitle = $objFirst ? $objFirst->headline : '';
        $this->Template->lastTitle = $objLast ? $objLast->headline : '';
        $this->Template->prevTitle = $objPrev ? $objPrev->headline : '';
        $this->Template->nextTitle = $objNext ? $objNext->headline : '';
        $this->Template->objFirst = $objFirst;
        $this->Template->objLast = $objLast;
        $this->Template->objPrev = $objPrev;
        $this->Template->objNext = $objNext;
    }

    private function isPreviewMode(): bool
    {
        return System::getContainer()->get('contao.security.token_checker')->isPreviewMode();
    }
}
