<?php

/**
 * IcsFile
 * php version 7.2.28
 */
class IcsFile
{
    /**
     * @var string
     */
    private $file_name;

    /**
     * @var string
     */
    private $data;

    /**
     * Creates an ics file object, including a file name and file data 
     *
     * @param string $event_name
     * @param string $start
     * @param string $end
     * @param string $description
     * @param string $location
     * @param int $event_id
     * @param int $timeslot_id
     * @param int $attendee_id
     * @return void
     */
    public function __construct($event_name,$start,$end,$description,$location,$event_id,$timeslot_id,$attendee_id)
    {
        $this->file_name = $event_name;
        $this->data = "BEGIN:VCALENDAR\nVERSION:2.0\nMETHOD:PUBLISH\nBEGIN:VEVENT\nDTSTART:".
        date("Ymd\THis",strtotime($start))."\nDTEND:".date("Ymd\THis",strtotime($end)).
        "\nLOCATION:".$location."\nTRANSP: OPAQUE\nSEQUENCE:0\nUID:".$event_id."-".$timeslot_id."-"
        .$attendee_id."\nDTSTAMP:".date("Ymd\THis\Z")."\nSUMMARY:".$event_name."\nDESCRIPTION:"
        .$description."\nPRIORITY:1\nCLASS:PUBLIC\nEND:VEVENT\nEND:VCALENDAR\n";
    }

    /**
     * Serves the Ics file object to the user, allowing the user to open or save the file
     *
     * @return void
     */
    public function serveIcsFile() 
    {
        header('Content-Type:text/calendar');
        header('Content-Disposition: attachment; filename="'.$this->file_name.'.ics"');
        header('Content-Length: '.strlen($this->data));
        header('Connection: close');
        echo $this->data;
    }
}
?>