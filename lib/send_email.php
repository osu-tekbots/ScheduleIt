<?php

/**
 * SendEmail
 * php version 7.2.28
 */
class SendEmail
{
    /**
     * @var object
     */
    private $send_email;

    /**
     * Send invite confirmed email.
     *
     * @param object $meeting
     * @return void
     */
    public function inviteConfirmed($meeting)
    {
        $start_time = new DateTime($meeting['start_time']);
        $end_time = new DateTime($meeting['end_time']);
        $start_time->setTimezone(new DateTimeZone($_SESSION['user_timezone']));
        $end_time->setTimezone(new DateTimeZone($_SESSION['user_timezone']));

        $to = $meeting['attendee_email'];
        // $to = 'bounce';
        $subject = 'Confirmed: ' . $meeting['name'];
        $headers = 'From: ' . SITE_NAME . ' <no-reply@oregonstate.edu>' . "\r\n" .
            'Cc: ' . $meeting['creator_name'] . '<' . $meeting['creator_email'] . '>' . "\r\n" .
            'Reply-To: ' . $meeting['creator_name'] . '<' . $meeting['creator_email'] . '>' . "\r\n" .
            // 'Return-Path: tekbot-web@oregonstate.edu' . "\r\n" .
            'X-Mailer: PHP/' . phpversion();

        $message = 'Hi ' . $meeting['attendee_name'] . ',' . "\r\n\r\n";
        $message .= 'You have reserved a timeslot for "' . $meeting['name'] . '".' . "\r\n\r\n";
        $message .= 'Date: ' . $start_time->format('D, F j, Y g:ia') . '-' . $end_time->format('g:ia T (\G\M\T P)') . "\r\n";
        $message .= 'Location: ' . $meeting['location'] . "\r\n";
        $message .= 'Creator: ' . $meeting['creator_name'] . ' (' . $meeting['creator_email'] . ")\r\n\r\n";
        $message .= 'Meeting Info (including add-to-calendar links): ' . SITE_URL . '/invite?key=' . $meeting['meeting_hash'] . "\r\n";

        mail($to, $subject, $message, $headers);
    }

    /**
     * Send invite updated email.
     *
     * @param object $meeting
     * @return void
     */
    public function inviteUpdated($meeting)
    {
        $start_time = new DateTime($meeting['start_time']);
        $end_time = new DateTime($meeting['end_time']);
        $start_time->setTimezone(new DateTimeZone($_SESSION['user_timezone']));
        $end_time->setTimezone(new DateTimeZone($_SESSION['user_timezone']));

        $to = $meeting['attendee_email'];
        $subject = 'Updated: ' . $meeting['name'];
        $headers = 'From: ' . SITE_NAME . ' <no-reply@oregonstate.edu>' . "\r\n" .
            'Cc: ' . $meeting['creator_name'] . '<' . $meeting['creator_email'] . '>' . "\r\n" .
            'Reply-To: ' . $meeting['creator_name'] . '<' . $meeting['creator_email'] . '>' . "\r\n" .
            //   'Return-Path: tekbot-web@oregonstate.edu' . "\r\n" .
            'X-Mailer: PHP/' . phpversion();

        $message = 'Hi ' . $meeting['attendee_name'] . ',' . "\r\n\r\n";
        $message .= 'You have updated your timeslot for "' . $meeting['name'] . '".' . "\r\n\r\n";
        $message .= 'Date: ' . $start_time->format('D, F j, Y g:ia') . '-' . $end_time->format('g:ia T (\G\M\T P)') . "\r\n";
        $message .= 'Location: ' . $meeting['location'] . "\r\n";
        $message .= 'Creator: ' . $meeting['creator_name'] . "\r\n\r\n";
        $message .= 'Meeting Info: ' . SITE_URL . '/invite?key=' . $meeting['meeting_hash'] . "\r\n";

        mail($to, $subject, $message, $headers);
    }

    /**
     * Send invite email.
     *
     * @param string $creatorOnid
     * @param string $inviteOnid
     * @param string $eventName
     * @param string $creatorName
     * @param string $link
     * @return void
     */
    public function invitation($creatorOnid, $inviteOnid, $eventName, $creatorName, $link)
    {
        $to = $inviteOnid . '@oregonstate.edu';
        $subject = 'Invited: ' . $eventName;
        $headers = 'From: ' . SITE_NAME . ' <no-reply@oregonstate.edu>' . "\r\n" .
          'Reply-To: ' . $creatorName . '<' . $creatorOnid . '@oregonstate.edu' . '>' . "\r\n" .
        //   'Return-Path: tekbot-web@oregonstate.edu' . "\r\n" .
          'X-MAiler: PHP/' . phpversion();

        // TODO: get username
        $message = 'Hi ' . $inviteOnid . ', ' . "\r\n\r\n";
        $message .= $creatorName . ' has invited you to select an appointment time for the event "' . $eventName . '" meeting.' . "\r\n";
        $message .= 'This appointment is on the Oregon State scheduling tool Schedule-It.' . "\r\n";
        $message .= 'To go directly to this meeting appointment, follow the link below.' . "\r\n";
        $message .= 'You may also view all meeting invites after logging into Schedule-It at https://eecs.engineering.oregonstate.edu/education/schedule-it.' . "\r\n\r\n";
        $message .= 'Sign Up: ' . $link . "\r\n";

        mail($to, $subject, $message, $headers);
    }

    /**
     * Send removed attendee email.
     *
     * @param string $removeOnid
     * @param string $creatorName
     * @param string $creatorOnid
     * @param string $eventName
     * @return void
     */
    public function notifyRemovedAttendee($removeOnid, $creatorName, $creatorOnid, $eventName)
    {
        $to = $removeOnid . '@oregonstate.edu';
        $subject = 'Removed: ' . trim($eventName);
        $headers = 'From: ' . SITE_NAME . ' <no-reply@oregonstate.edu>' . "\r\n" .
          'Cc: ' . $meeting['creator_name'] . '<' . $meeting['creator_email'] . '>' . "\r\n" .
          'Reply-To: ' . $creatorName . '<' . $creatorOnid . '@oregonstate.edu' . '>' . "\r\n" .
        //   'Return-Path: tekbot-web@oregonstate.edu' . "\r\n" .
          'X-MAiler: PHP/' . phpversion();

        $message = 'Hi ' . $removeOnid . ', ' . "\r\n\r\n";
        $message .= $creatorName . ' has removed you from the "' . trim($eventName) . '" meeting.' . "\r\n\r\n";
        $message .= 'If you have any question please contact them at ' . "\r\n";
        $message .= $creatorOnid . '@oregonstate.edu' . "\r\n";

        mail($to, $subject, $message, $headers);
    }

    /**
     * Inform collaborators that they've been added to the meeting.
     *
     * @param string $inviteName
     * @param string $inviteEmail
     * @param string $eventName
     * @param string $creatorName
     * @param string $creatorEmail
     * @param string $link
     * @return void
     */
    public function addMeetingCollaborator($inviteName, $inviteEmail, $eventName, $creatorName, $creatorEmail, $link)
    {
        $subject = 'Added as Collaborator: ' . $eventName;
        $headers = 'From: ' . SITE_NAME . ' <no-reply@oregonstate.edu>' . "\r\n" .
          'Reply-To: ' . $creatorName . '<' . $creatorEmail . '>' . "\r\n" .
          'X-MAiler: PHP/' . phpversion();

        $message = 'Hi ' . $inviteName . ', ' . "\r\n\r\n";
        $message .= $creatorName . ' has added you as a collaborator for the meeting "' . $eventName . '".' . "\r\n";
        $message .= 'This meeting is on the Oregon State scheduling tool Schedule-It.' . "\r\n";
        $message .= 'To go directly to this meeting, follow the link below.' . "\r\n";
        $message .= 'You may also view all of your meetings after logging into Schedule-It at eecs.engineering.oregonstate.edu/education/schedule-it.' . "\r\n\r\n";
        $message .= 'View meeting: ' . $link . "\r\n";

        mail($inviteEmail, $subject, $message, $headers);
    }

    /**
     * Inform collaborators that they've been removed from the meeting.
     *
     * @param string $collaboratorName
     * @param string $collaboratorEmail
     * @param string $eventName
     * @param string $creatorName
     * @param string $creatorEmail
     * @return void
     */
    public function removeMeetingCollaborator($collaboratorName, $collaboratorEmail, $eventName, $creatorName, $creatorEmail)
    {
        $subject = 'Removed as Collaborator: ' . $eventName;
        $headers = 'From: ' . SITE_NAME . ' <no-reply@oregonstate.edu>' . "\r\n" .
          'Reply-To: ' . $creatorName . '<' . $creatorEmail . '>' . "\r\n" .
          'X-MAiler: PHP/' . phpversion();

        $message = 'Hi ' . $collaboratorName . ', ' . "\r\n\r\n";
        $message .= $creatorName . ' has removed you as a collaborator for the meeting "' . $eventName . '" on Schedule-It.' . "\r\n";
        $message .= "If you believe this was in error, please contact them at $creatorEmail." . "\r\n";

        mail($collaboratorEmail, $subject, $message, $headers);
    }

    /**
     * Send timeslots changed email.
     *
     * @param object $meeting
     * @return void
     */
    public function changedTimeslots($meeting, $user)
    {
        $to = $user['attendee_email'];
        $subject = 'Changed: ' . $meeting['name'];
        $headers = 'From: ' . SITE_NAME . ' <no-reply@oregonstate.edu>' . "\r\n" .
            'Cc: ' . $meeting['creator_name'] . '<' . $meeting['creator_email'] . '>' . "\r\n" .
            'Reply-To: ' . $meeting['creator_name'] . '<' . $meeting['creator_email'] . '>' . "\r\n" .
        //  'Return-Path: tekbot-web@oregonstate.edu' . "\r\n" .
            'X-Mailer: PHP/' . phpversion();

        $message = 'Hi ' . $user['attendee_name'] . ',' . "\r\n\r\n";
        $message .= 'The available timeslots for "' . $meeting['name'] . '" have changed and your reservation is no longer available. Please sign up for a new timeslot.' . "\r\n\r\n";
        $message .= 'Sign Up: ' . SITE_URL . '/invite?key=' . $meeting['meeting_hash'] . "\r\n";

        mail($to, $subject, $message, $headers);
    }

    /**
     * Send email when find-a-time schedule is reset.
     * 
     * @param string $scheduleName
     * @param string $creatorName
     * @param string $creatorEmail
     * @param array{mixed} $respondents
     * @return void
     */
    public function scheduleReset($scheduleName, $creatorName, $creatorEmail, $respondents) {
        foreach ($respondents as $user) {
            $to = $user['email'];
            $subject = 'Re-enter availability: ' . trim($scheduleName);
            $headers = 'From: ' . SITE_NAME . ' <no-reply@oregonstate.edu>' . "\r\n" .
            'Cc: ' . $creatorName . '<' . $creatorEmail . '>' . "\r\n" .
            'Reply-To: ' . $creatorName . '<' . $creatorEmail . '>' . "\r\n" .
            //   'Return-Path: tekbot-web@oregonstate.edu' . "\r\n" .
            'X-MAiler: PHP/' . phpversion();

            $message = 'Hi ' . $user['first_name'].' '.$user['last_name'] . ', ' . "\r\n\r\n";
            $message .= $creatorName . ' has changed the dates for the "' . trim($scheduleName) . '" find-a-time.' . "\r\n\r\n";
            $message .= 'Please enter your availability for the new date ranges.' . "\r\n\r\n";
            $message .= 'If you have any questions, please contact ' . $creatorName . ' at ' . "\r\n";
            $message .= $creatorEmail . "\r\n";

            mail($to, $subject, $message, $headers);
        }
    }
}

$send_email = new SendEmail();
