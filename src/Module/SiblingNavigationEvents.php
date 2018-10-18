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

use Contao\BackendTemplate;
use Contao\CalendarEventsModel;
use Contao\Config;
use Contao\Events;
use Contao\Input;
use Contao\StringUtil;
use Patchwork\Utf8;

class SiblingNavigationEvents extends Events
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

            $objTemplate->wildcard = '### '. Utf8::strtoupper($GLOBALS['TL_LANG']['FMD']['sibling_navigation_events'][0]). ' ###';

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

        $this->cal_calendar = $this->sortOutProtected(StringUtil::deserialize($this->cal_calendar));

        if (!\is_array($this->cal_calendar) || empty($this->cal_calendar))
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
        $objCurrent = CalendarEventsModel::findByIdOrAlias(Input::get('items'));

        // Check if calendar of current event is within the enabled calendars
        if (!in_array($objCurrent->pid, $this->cal_calendar))
        {
            $this->cal_calendar = [$objCurrent->pid];
        }

        // Get all events
        $arrAllEvents = $this->getAllEvents($this->cal_calendar, 0, PHP_INT_MAX);
        ksort($arrAllEvents);

        // Search for previous and next event
        $arrPrev  = null;
        $arrNext  = null;
        $blnFound = false;

        foreach ($arrAllEvents as $day)
        {
            ksort($day);
            foreach ($day as $arrEvents)
            {
                foreach ($arrEvents as $event)
                {
                    if ($blnFound)
                    {
                        $arrNext = $event;
                        break;
                    }

                    if ($event['id'] == $objCurrent->id)
                    {
                        $blnFound = true;
                        continue;
                    }

                    $arrPrev = $event;
                }

                if ($arrNext)
                {
                    break;
                }
            }

            if ($arrNext)
            {
                break;
            }
        }

        $objPrev = $arrPrev ? CalendarEventsModel::findById($arrPrev['id']) : null;
        $objNext = $arrNext ? CalendarEventsModel::findById($arrNext['id']) : null;

        $this->Template->prev = $objPrev ? Events::generateEventUrl($objPrev) : null;
        $this->Template->next = $objNext ? Events::generateEventUrl($objNext) : null;
        $this->Template->prevTitle = $objPrev ? $objPrev->title : '';
        $this->Template->nextTitle = $objNext ? $objNext->title : '';
        $this->Template->objPrev = $objPrev;
        $this->Template->objNext = $objNext;
    }
}
