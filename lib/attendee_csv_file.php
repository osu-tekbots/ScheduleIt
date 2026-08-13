<?php

class AttendeeCsvFile
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
     * Creates an csv file object, including a file name and file data 
     *
     * @param object $meeting 
     * @param object $timeslots 
     * @return void
     */
    public function __construct($meeting, $attendee_meetings)
    {
        $this->file_name = $meeting['name'];
        $this->data = [['Start Time', 'End Time', 'Attendee Name', 'Attendee Email', 'Attendee ONID']];
        
        foreach ($attendee_meetings as $attendee) {
            $this->data[] = [
                date('Y-m-d H:i:s', strtotime($attendee['start_time'])),
                date('Y-m-d H:i:s', strtotime($attendee['end_time'])),
                $attendee['attendee_name'],
                $attendee['attendee_email'],
                $attendee['attendee_onid'],
            ];
        }
    }

    /**
     * Serves the Ics file object to the user, allowing the user to open or save the file
     *
     * @return void
     */
    public function serveCsvFile() 
    {
        header('Content-Type:text/csv');
        header('Content-Disposition: attachment; filename="'.$this->file_name.'.csv"');
        header('Connection: close');
        
        if (ob_get_level()) ob_end_clean();

        $output = fopen('php://output', 'w');
        foreach ($this->data as $row) {
            fputcsv($output, $row);
        }
        fclose($output);

        exit;
    }
}
