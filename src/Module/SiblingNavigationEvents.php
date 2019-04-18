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
            Input::setGet('items', Input::get('auto_item', false, true));
        }

        if (!Input::get('items', false, true))
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
        $currentEvent = CalendarEventsModel::findByIdOrAlias(Input::get('items', false, true));

        // Check if calendar of current event is within the enabled calendars
        if (!in_array($currentEvent->pid, $this->cal_calendar))
        {
            $this->cal_calendar = [$currentEvent->pid];
        }

        // Get all events
        $days = $this->getAllEvents($this->cal_calendar, 0, PHP_INT_MAX);
        ksort($days);

        // Search for previous and next event
        $prev  = null;
        $next  = null;
        $flatEvents = [];

        foreach ($days as $day)
        {
            ksort($day);
            foreach ($day as $events)
            {
                foreach ($events as $event)
                {
                    $flatEvents[$event['id']] = $event;
                }
            }
        }

        $flatEvents = array_values($flatEvents);

        for ($i = 0; $i < count($flatEvents); ++$i) {
            if ($flatEvents[$i]['id'] == $currentEvent->id) {
                if ($i > 0) {
                    $prev = $flatEvents[$i - 1];
                }
                if ($i < count($flatEvents) -1) {
                    $next = $flatEvents[$i + 1];
                }
                break;
            }
        }

        $prev = $prev ? CalendarEventsModel::findById($prev['id']) : null;
        $next = $next ? CalendarEventsModel::findById($next['id']) : null;

        $this->Template->prev = $prev ? Events::generateEventUrl($prev) : null;
        $this->Template->next = $next ? Events::generateEventUrl($next) : null;
        $this->Template->prevTitle = $prev ? $prev->title : '';
        $this->Template->nextTitle = $next ? $next->title : '';
        $this->Template->objPrev = $prev;
        $this->Template->objNext = $next;
    }
}
