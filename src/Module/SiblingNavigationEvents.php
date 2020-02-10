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
     * Current event.
     *
     * @var CalendarEventsModel
     */
    protected $currentEvent;

    /**
     * Display a wildcard in the back end.
     *
     * @return string
     */
    public function generate()
    {
        if (TL_MODE === 'BE') {
            $objTemplate = new BackendTemplate('be_wildcard');

            $objTemplate->wildcard = '### '.Utf8::strtoupper($GLOBALS['TL_LANG']['FMD']['sibling_navigation_events'][0]).' ###';

            $objTemplate->title = $this->headline;
            $objTemplate->id = $this->id;
            $objTemplate->link = $this->name;
            $objTemplate->href = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id='.$this->id;

            return $objTemplate->parse();
        }

        // Set the item from the auto_item parameter
        $item = null;
        if (!isset($_GET['items']) && Config::get('useAutoItem') && isset($_GET['auto_item'])) {
            $item = Input::get('auto_item', false, true);
        } else {
            $item = Input::get('items', false, true);
        }

        if (null === $item) {
            return '';
        }

        $this->cal_calendar = $this->sortOutProtected(StringUtil::deserialize($this->cal_calendar, true));

        $this->currentEvent = CalendarEventsModel::findByIdOrAlias($item);

        if (null === $this->currentEvent) {
            return '';
        }

        return parent::generate();
    }

    /**
     * Generate the module.
     */
    protected function compile(): void
    {
        // Check if calendar of current event is within the enabled calendars
        if (!\in_array($this->currentEvent->pid, $this->cal_calendar, true)) {
            $this->cal_calendar = [$this->currentEvent->pid];
        }

        // Get all events, 1970-01-01 00:00:00 - 2038-01-01 00:00:00
        $days = $this->getAllEvents($this->cal_calendar, 0, 2145913200);
        ksort($days);

        // Search for previous and next event
        $prev = null;
        $next = null;
        $flatEvents = [];

        foreach ($days as $day) {
            ksort($day);
            foreach ($day as $events) {
                foreach ($events as $event) {
                    $flatEvents[$event['id']] = $event;
                }
            }
        }

        $flatEvents = array_values($flatEvents);

        for ($i = 0; $i < \count($flatEvents); ++$i) {
            if ((int) $flatEvents[$i]['id'] === (int) $this->currentEvent->id) {
                if ($i > 0) {
                    $prev = $flatEvents[$i - 1];
                }
                if ($i < \count($flatEvents) - 1) {
                    $next = $flatEvents[$i + 1];
                }
                break;
            }
        }

        $first = null;
        $last = null;

        if (\count($flatEvents) > 0) {
            $firstEntry = $flatEvents[0];

            if ((int) $firstEntry['id'] !== (int) $this->currentEvent->id && null !== $prev) {
                if ((int) $firstEntry['id'] !== $prev->id) {
                    $first = CalendarEventsModel::findById($firstEntry['id']);
                }
            }

            $lastEntry = $flatEvents[\count($flatEvents) - 1];

            if ((int) $lastEntry['id'] !== (int) $this->currentEvent->id && null !== $next) {
                if ((int) $lastEntry['id'] !== $next->id) {
                    $last = CalendarEventsModel::findById($lastEntry['id']);
                }
            }
        }

        $prev = $prev ? CalendarEventsModel::findById($prev['id']) : null;
        $next = $next ? CalendarEventsModel::findById($next['id']) : null;

        $this->Template->first = $first ? Events::generateEventUrl($first) : null;
        $this->Template->last = $last ? Events::generateEventUrl($last) : null;
        $this->Template->prev = $prev ? Events::generateEventUrl($prev) : null;
        $this->Template->next = $next ? Events::generateEventUrl($next) : null;
        $this->Template->firstTitle = $first ? $first->title : '';
        $this->Template->lastTitle = $last ? $last->title : '';
        $this->Template->prevTitle = $prev ? $prev->title : '';
        $this->Template->nextTitle = $next ? $next->title : '';
        $this->Template->objFirst = $first;
        $this->Template->objLast = $last;
        $this->Template->objPrev = $prev;
        $this->Template->objNext = $next;
    }
}
