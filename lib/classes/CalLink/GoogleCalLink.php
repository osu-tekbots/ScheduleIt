<?php

require_once __DIR__ . '/CalLink.php';

/**
 * GoogleCalLink
 * php version 7.2.28
 */
class GoogleCalLink extends CalLink
{
    /**
     * Converts SQL time format to google link time format
     *
     * @param string $time
     * @return string $newTime
     */
    protected function convertTime($time)
    {
        return date('Ymd\THis', strtotime($time));
    }

    public function getOwnerLink()
    {
        $description = urlencode(
            implode('<BR>', $this->requiredAttendees) . '<BR>' . implode('<BR>', $this->optionalAttendees) . '<BR><BR>'
        ) . $this->description;
        $requiredAttendees = implode(',', $this->requiredAttendees);
        $optionalAttendees = count($this->optionalAttendees) ? '('.implode(',', $this->optionalAttendees).')' : '';

        return "https://calendar.google.com/calendar/u/0/r/eventedit?text={$this->event_name}&details={$description}"
            . "&dates={$this->start}/{$this->end}&location={$this->location}"
            . "&add=$requiredAttendees,$optionalAttendees";
    }

    public function getLink()
    {
        return "https://calendar.google.com/calendar/u/0/r/eventedit?text={$this->event_name}&details={$this->description}"
            . "&dates={$this->start}/{$this->end}&location={$this->location}";
    }
}

?>