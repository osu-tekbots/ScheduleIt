<?php

require_once ABSPATH . 'config/session.php';
require_once ABSPATH . 'lib/file_upload.php';

$meeting = $database->getMeetingById($meeting_id, $_SESSION['user_onid']);

if ($meeting) {
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if (isset($_POST['deleteHash'])) {
            $meetingHash = $_POST['deleteHash'];
            $meetingHash = trim($meetingHash);

            $result = $database->deleteMeeting($meetingHash);

            if ($result > 0) {
                // delete event files here
                $file_upload->deleteEventFiles($meetingHash);
                $msg->success('"' . $meeting['name'] . '" has been deleted.', SITE_DIR . '/manage');
            } else {
                $msg->error('Could not delete the meeting.');
            }
        } else {
            $meeting['id'] = $meeting_id;
            $meeting['name'] = $_POST['name'];
            $meeting['location'] = $_POST['location'];
            $meeting['description'] = $_POST['description'];
            $meeting['is_anon'] = $_POST['is_anon'] == '1';
            $meeting['enable_message'] = !empty($_POST['enable_message']) ? 1 : 0;
            $meeting['require_message'] = !empty($_POST['require_message']) ? 1 : 0;
            $meeting['message_prompt'] = $_POST['message_prompt'];
            $meeting['enable_upload'] = $_POST['enable_upload'] == '1';
            $meeting['require_upload'] = $_POST['require_upload'] == '1';

            if (empty($_POST['name']) || empty($_POST['location'])) {
                $msg->error('Please fill out all required fields.');
            } else {
                $updated_meeting = $database->updateMeeting($_SESSION['user_id'], $meeting);

                // Check for file to upload
                if (!empty($_FILES['file']['name'])) {
                    // Upload file
                    $new_file_upload = $file_upload->upload($_SESSION['user_onid'], $meeting['hash']);

                    if ($new_file_upload['error']) {
                        $msg->error($new_file_upload['message']);
                    } else {
                        $msg->success('"' . $meeting['name'] . '" has been updated.', SITE_DIR . '/meetings/' . $meeting_id);
                    }
                // No file uploaded, just meeting update
                } elseif ($updated_meeting > -1) {
                    $msg->success('"' . $meeting['name'] . '" has been updated.', SITE_DIR . '/meetings/' . $meeting_id);
                } else {
                    $msg->error('Could not update meeting.');
                }
            }
        }
    }

    echo $twig->render('meetings/edit.twig', [
        'meeting' => $meeting,
        'title' => 'Edit Meeting - ' . $meeting['name'],
    ]);
} else {
    http_response_code(404);
    echo $twig->render('errors/error_logged_in.twig', [
        'message' => 'Sorry, we couldn\'t find that meeting.',
        'title' => 'Meeting Not Found',
    ]);
}
