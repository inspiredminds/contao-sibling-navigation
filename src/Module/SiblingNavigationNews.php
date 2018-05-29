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

namespace InspiredMinds\ContaoSiblingNavigation\Module;

use Codefog\NewsCategoriesBundle\CodefogNewsCategoriesBundle;
use Contao\BackendTemplate;
use Contao\Config;
use Contao\Input;
use Contao\ModuleNews;
use Contao\News;
use Contao\NewsModel;
use Contao\StringUtil;
use Contao\System;
use Haste\Model\Model as HasteModel;
use Patchwork\Utf8;

class SiblingNavigationNews extends ModuleNews
{
    /**
     * Template.
     *
     * @var string
     */
    protected $strTemplate = 'mod_sibling_navigation';


    /**
     * Display a wildcard in the back end.
     *
     * @return string
     */
    public function generate()
    {
        if (TL_MODE == 'BE')
        {
            $objTemplate = new BackendTemplate('be_wildcard');

            $objTemplate->wildcard = '### '. Utf8::strtoupper($GLOBALS['TL_LANG']['FMD']['sibling_navigation_news'][0]). ' ###';

            $objTemplate->title = $this->headline;
            $objTemplate->id = $this->id;
            $objTemplate->link = $this->name;
            $objTemplate->href = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;

            return $objTemplate->parse();
        }

        // Set the item from the auto_item parameter
        if (!isset($_GET['items']) && Config::get('useAutoItem') && isset($_GET['auto_item']))
        {
            Input::setGet('items', Input::get('auto_item'));
        }

        if (!Input::get('items'))
        {
            return '';
        }

        $this->news_archives = $this->sortOutProtected(StringUtil::deserialize($this->news_archives));

        if (!\is_array($this->news_archives) || empty($this->news_archives))
        {
            return '';
        }

        return parent::generate();
    }


    /**
     * Generate the module.
     */
    protected function compile()
    {
        // Get the current news item
        $objCurrent = NewsModel::findByIdOrAlias(Input::get('items'));

        // Check if archive of current news item is within the enabled archives
        if (!in_array($objCurrent->pid, $this->news_archives))
        {
            $this->news_archives = [$objCurrent->pid];
        }

        $t = NewsModel::getTable();

        // Basic query definition
        $arrQuery = [
            'column' => [
                "$t.pid IN(" . \implode(',', \array_map('intval', $this->news_archives)) . ")",
                "$t.published = '1'",
            ],
            'limit'  => 1,
            'return' => 'Model',
        ];

        // Get category parameter
        $strCategory = Input::get('category');

        // Check for category input
        $bundles = System::getContainer()->getParameter('kernel.bundles');
        if ($strCategory && in_array(CodefogNewsCategoriesBundle::class, $bundles))
        {
            $arrCategories = StringUtil::trimsplit(',', $strCategory);
            $arrCategoryNewsIds = [];

            // Go through each category
            foreach ($arrCategories as $category)
            {
                // Get the news items for this category
                $arrNewsIds = HasteModel::getReferenceValues('tl_news', 'categories', $category);

                // Intersect all news IDs (ignoring empty ones)
                if ($arrCategoryNewsIds && $arrNewsIds)
                {
                    $arrCategoryNewsIds = \array_intersect($arrCategoryNewsIds, $arrNewsIds);
                }
                elseif (!$arrCategoryNewsIds)
                {
                    $arrCategoryNewsIds = $arrNewsIds;
                }
            }

            $arrCategoryNewsIds = \array_map('intval', $arrCategoryNewsIds); 
            $arrCategoryNewsIds = \array_filter($arrCategoryNewsIds);
            $arrCategoryNewsIds = \array_unique($arrCategoryNewsIds);

            if ($arrCategoryNewsIds)
            {
                $arrQuery['column'][] = "$t.id IN(" . \implode(',', $arrCategoryNewsIds) . ")";
            }
        }

        $arrQueryPrev = $arrQuery;
        $arrQueryNext = $arrQuery;

        // support for news_sorting and news_order
        $this->news_order = $this->news_sorting ?: $this->news_order;
        switch ($this->news_order)
        {
            case 'sort_date_asc':
            case 'order_date_asc':
                $arrQueryPrev['column'][] = "$t.date > ?"; 
                $arrQueryPrev['value'][] = $objCurrent->date;
                $arrQueryPrev['order'] = "$t.date ASC";
                $arrQueryNext['column'][] = "$t.date < ?";
                $arrQueryNext['value'][] = $objCurrent->date;
                $arrQueryNext['order'] = "$t.date DESC";
                break;

            case 'sort_headline_asc':
            case 'order_headline_asc':
                $arrQueryPrev['column'][] = "$t.headline > ?"; 
                $arrQueryPrev['value'][] = $objCurrent->headline;
                $arrQueryPrev['order'] = "$t.headline ASC";
                $arrQueryNext['column'][] = "$t.headline < ?";
                $arrQueryNext['value'][] = $objCurrent->headline;
                $arrQueryNext['order'] = "$t.headline DESC";
                break;

            case 'sort_headline_desc':
            case 'order_headline_desc':
                $arrQueryPrev['column'][] = "$t.headline < ?"; 
                $arrQueryPrev['value'][] = $objCurrent->headline;
                $arrQueryPrev['order'] = "$t.headline DESC";
                $arrQueryNext['column'][] = "$t.headline > ?";
                $arrQueryNext['value'][] = $objCurrent->headline;
                $arrQueryNext['order'] = "$t.headline ASC";
                break;

            default:
                $arrQueryPrev['column'][] = "$t.date < ?"; 
                $arrQueryPrev['value'][] = $objCurrent->date;
                $arrQueryPrev['order'] = "$t.date DESC";
                $arrQueryNext['column'][] = "$t.date > ?";
                $arrQueryNext['value'][] = $objCurrent->date;
                $arrQueryNext['order'] = "$t.date ASC";
        }

        $objPrev = NewsModel::findAll($arrQueryPrev);
        $objNext = NewsModel::findAll($arrQueryNext);

        $strPrevLink = $objPrev ? News::generateNewsUrl($objPrev) . ($strCategory ? '?category='.$strCategory : '') : null;
        $strNextLink = $objNext ? News::generateNewsUrl($objNext) . ($strCategory ? '?category='.$strCategory : '') : null;

        $this->Template->prev = $strPrevLink;
        $this->Template->next = $strNextLink;
        $this->Template->prevTitle = $objPrev ? $objPrev->headline : '';
        $this->Template->nextTitle = $objNext ? $objNext->headline : '';
        $this->Template->objPrev = $objPrev;
        $this->Template->objNext = $objNext;
    }
}
