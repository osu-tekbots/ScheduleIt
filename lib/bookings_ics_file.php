<?php

/**
 * IcsFile
 * php version 7.2.28
 */
class BookingsIcsFile
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
     * @param object $meeting 
     * @param object $timeslots 
     * @return void
     */
    public function __construct($meeting, $timeslots)
    {
        $this->file_name = $meeting['name'];
        $this->data = "BEGIN:VCALENDAR\nVERSION:2.0\nMETHOD:PUBLISH";
        foreach ($timeslots as $key => $slot) {
            $this->data .= "\nBEGIN:VEVENT\nDTSTART:".date("Ymd\THis",strtotime($timeslots[$key]['start_time'])).
            "\nDTEND:".date("Ymd\THis",strtotime($timeslots[$key]['end_time'])).
            "\nTRANSP: OPAQUE\nSEQUENCE:0\nSUMMARY:".$meeting['name']."\nDESCRIPTION:".$meeting['description']."\nLOCATION:"
            .$meeting['location']."\nUID:".$meeting['id']."-".$timeslots[$key]['id']
            ."\nDTSTAMP:".date("Ymd\THis\Z")."\nPRIORITY:1\nCLASS:PUBLIC\nEND:VEVENT";
        }
        $this->data .= "\nEND:VCALENDAR\n";
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