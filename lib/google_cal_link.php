<?php

/**
 * GoogleCalLink
 * php version 7.2.28
 */
class GoogleCalLink
{
    /**
     * @var string
     */
    private $link;

    /**
     * Converts SQL time format to google link time format
     *
     * @param string $time
     * @return string $newTime
     */
    private function convertTime($time)
    {
        $newTime = substr($time, 0, 4).substr($time, 5, 2).substr($time, 8, 2)."T".substr($time, 11, 2).substr($time, 14, 2).substr($time, 17, 2)."Z";
        return $newTime;
    }

    /**
     * Creates a google calendar link object
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
        $this->link = "https://calendar.google.com/calendar/u/0/r/eventedit?text=".$event_name."&location=".$location."&details=".$description."&dates=".$convertStart."/".$convertEnd;
    }

    public function getLink() 
    {
        return $this->link;
    }
}

?>