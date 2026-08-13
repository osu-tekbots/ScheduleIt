<?php

require_once __DIR__ . '/CalLink.php';

/**
 * OutlookCalLink
 * php version 7.2.28
 */
class OutlookCalLink extends CalLink
{
    /**
     * Converts SQL time format to outlook link time format
     *
     * @param string $time
     * @return string $newTime
     */
    protected function convertTime($time)
    {
        return date('Y-m-d\TH%3\Ai', strtotime($time));
    }

    public function getOwnerLink()
    {
        $description = urlencode(
            implode('<BR>', $this->requiredAttendees) . '<BR>' . implode('<BR>', $this->optionalAttendees) . '<BR><BR>'
        ) . $this->description;
        $requiredAttendees = implode(',', $this->requiredAttendees);
        $optionalAttendees = implode(',', $this->optionalAttendees);

        return "https://outlook.office.com/calendar/0/action/compose?subject={$this->event_name}&body={$description}"
            ."&startdt={$this->start}&enddt={$this->end}&location={$this->location}"
            ."&to={$requiredAttendees}&cc={$optionalAttendees}"
            ."&path=%2Fcalendar%2Faction%2Fcompose&rru=addevent";
    }

    public function getLink()
    {
        return "https://outlook.office.com/calendar/0/action/compose?subject={$this->event_name}&body={$this->description}"
            ."&startdt={$this->start}&enddt={$this->end}&location={$this->location}"
            ."&path=%2Fcalendar%2Faction%2Fcompose&rru=addevent";
    }
}
