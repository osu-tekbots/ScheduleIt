<?php

abstract class CalLink
{
    /** @var string */
    protected $event_name;
    
    /** @var string */
    protected $description;

    /** @var string */
    protected $start;

    /** @var string */
    protected $end;

    /** @var string */
    protected $location;

    /** @var array{string} */
    protected $requiredAttendees;

    /** @var array{string} */
    protected $optionalAttendees;

    /**
     * Converts SQL time format to calendar link time format
     *
     * @param string $time
     * @return string $newTime
     */
    abstract protected function convertTime($time);

    /**
     * Creates a calendar link object
     *
     * @param string $event_name
     * @param string $description
     * @param string $start
     * @param string $end
     * @param string $location
     * @return void
     */
    public function __construct($event_name, $description, $start, $end, $location)
    {
        $this->event_name = urlencode($event_name);
        $this->description = urlencode($description);
        $this->start = $this->convertTime($start);
        $this->end = $this->convertTime($end);
        $this->location = urlencode($location);
        $this->requiredAttendees = [];
        $this->optionalAttendees = [];
    }

    public function addRequiredAttendee($email)
    {
        $this->requiredAttendees[] = $email;
        return $this;
    }

    public function addOptionalAttendee($email)
    {
        $this->optionalAttendees[] = $email;
        return $this;
    }

    abstract public function getOwnerLink();

    abstract public function getLink();
}
