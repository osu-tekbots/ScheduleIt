<?php

/**
 * OutlookCalLink
 * php version 7.2.28
 */
class OutlookCalLink
{
    /**
     * @var string
     */
    private $link;

    /**
     * Converts SQL time format to outlook link time format
     *
     * @param string $time
     * @return string $newTime
     */
    private function convertTime($time)
    {
        $newTime = substr($time, 0, 10)."T".substr($time, 11, 2)."%3A".substr($time, 14, 2);
        return $newTime;
    }

    /**
     * Creates an outlook calendar link object
     *
     * @param string $event_name
     * @param string $start
     * @param string $end
     * @param string $description
     * @param string $location
     * @return void
     */
    public function __construct($event_name,$start,$end,$description,$location)
    {
        $convertStart = $this->convertTime($start);
        $convertEnd = $this->convertTime($end);
        $this->link = "https://outlook.office.com/calendar/0/action/compose?body=".$description."&enddt=".$convertEnd."&location=".$location."&path=%2Fcalendar%2Faction%2Fcompose&rru=addevent&startdt=".$convertStart."&subject=".$event_name;
    }

    public function getLink() 
    {
        return $this->link;
    }
}

?>