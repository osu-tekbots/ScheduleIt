<?php

require_once ABSPATH . 'config/session.php';
require_once ABSPATH . 'lib/file_upload.php';
require_once ABSPATH . 'lib/send_email.php';

$meeting_hash = !empty($_GET['key']) ? $_GET['key'] : null;

$meeting = $database->getMeetingByHash($meeting_hash);

if ($meeting) {
    $dates = [];
    $results = $database->getAvailableDates($meeting_hash);

    // Create date and timeslots objects
    foreach ($results as $key => $date) {
        $timeslots_and_attendees = [];
        $timeslots = $database->getAvailableTimeslots($meeting_hash, $date['date']);
        foreach($timeslots as $timeslot) {
            $attendees = $database->getAttendeesByTimeslot($timeslot['hash']);
            array_push($timeslots_and_attendees, ['timeslot' => $timeslot, 'attendees' => $attendees]);
        }
        $date_and_timeslots = [
            'date' => $date['date'],
            'timeslots' => $timeslots_and_attendees
        ];
        array_push($dates, $date_and_timeslots);
    }

    // Get booking and timeslot information
    $booking = $database->getMeetingForUserId($_SESSION['user_id'], $meeting_hash);

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Request from decline modal
        if (isset($_POST['decline_booking'])) {
            // Delete booking
            if (!empty($_POST['timeslot_hash'])) {
                $database->deleteBooking($_SESSION['user_onid'], $_POST['timeslot_hash']);
            }
            // Delete invite, if one exists
            $database->deleteInvite($_SESSION['user_onid'], $meeting['id']);
            // Redirect to My Meetings
            $msg->success('You declined "' . $meeting['name'] . '".', SITE_DIR . '/meetings');
        } elseif ($_SERVER['CONTENT_LENGTH'] > UPLOAD_SIZE_LIMIT) {
            $msg->error('File upload is too large.');
        } elseif (!empty($_POST['timeslot_id'])) {
            $booking_id = $_POST['booking_id'];
            $timeslot_id = $_POST['timeslot_id'];

            // Check that there's a valid file if it's required
            if ($meeting['require_upload'] && (empty($_FILES['file']['name']) || $file_upload->verifyUpload()['error'])) {
                $msg->error('Please upload a valid file to reserve your timeslot.');
            } elseif($meeting['require_message'] && (!isset($_POST['message']) || $_POST['message'] == '')) {
                $msg->error('Please include a message to reserve your timeslot.');
            } elseif(strcmp($database->getTimeslot($timeslot_id)['end_time'], date('Y-m-d H:i:s')) == -1) {
                /* Meeting slot is already in the past */
                $msg->error('That meeting timeslot is already over. Please pick a different one.');
            } else {

                // Add or update booking
                $updated_booking = $database->addBooking($_SESSION['user_id'], $timeslot_id);
    
                // If no booking id from POST, it's a new booking
                if (empty($booking_id)) {
                    $booking = $database->getMeetingForUserId($_SESSION['user_id'], $meeting_hash);
                    $booking_id = $booking['id'];
                    $send_email->inviteConfirmed($booking);
                }

                if(isset($_POST['message'])) {
                    $result = $database->addBookingMessage($booking_id, htmlspecialchars($_POST['message']));
                    if($result == -1) {
                        /* 
                         *  Probably will get overwritten by a later message; if so this whole section should
                         *  be extracted into a function to allow an early return
                         */
                        $msg->error('Failed to save your message. Please try again later.');
                    }
                }
    
                // Check for file to upload
                if (!empty($_FILES['file']['name'])) {
                    // Upload file
                    $new_file_upload = $file_upload->upload($_SESSION['user_onid'], $meeting['hash'], $booking_id);
    
                    if ($new_file_upload['error']) {
                        $msg->error($new_file_upload['message']);
                    } else {
                        // Delete invite, if one exists
                        $database->deleteInvite($_SESSION['user_onid'], $meeting['id']);
                        // Send confirmation email only if time updated
                        if (!empty($timeslot_id) && $timeslot_id != $booking['timeslot_id']) {
                            $send_email->inviteUpdated($booking);
                        }
                        $msg->success('Your settings have been saved for "' . $meeting['name'] . '".', SITE_DIR . '/meetings');
                    }
                // No file uploaded, just booking update
                } elseif ($updated_booking > -1) {
                    // Delete invite, if one exists
                    $database->deleteInvite($_SESSION['user_onid'], $meeting['id']);
                    // Send confirmation email only if time updated
                    if (!empty($timeslot_id) && $timeslot_id != $booking['timeslot_id']) {
                        $send_email->inviteUpdated($booking);
                    }
                    $msg->success('Your settings have been saved for "' . $meeting['name'] . '".', SITE_DIR . '/meetings');
                } else {
                    $msg->error('That timeslot is no longer available.');
                }
                
            }

        } else {
            $msg->error('Please select a time.');
        }
    }

    echo $twig->render('invites/show.twig', [
        'booking' => $booking,
        'dates' => $dates,
        'meeting' => $meeting,
        'title' => $meeting['name']
    ]);
} else {
    http_response_code(404);
    echo $twig->render('errors/error_logged_in.twig', [
        'message' => 'Sorry, we couldn\'t find that invite.',
        'title' => 'Invite Not Found',
    ]);
}
