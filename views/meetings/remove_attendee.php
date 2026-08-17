<?php

require_once ABSPATH . 'config/session.php';
require_once ABSPATH . 'lib/send_email.php';
require_once ABSPATH . 'lib/file_upload.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
     $removeOnid = trim($_POST['attendeeOnid']);
     $slotHash = trim($_POST['slotHash']);

     $meeting = $database->getMeetingBySlotHash($slotHash);
     $collaborators = $database->getMeetingCollaborators($meeting['id'], $meeting['hash']);

     $is_collaborator = !empty(array_filter(
          $collaborators,
          fn ($c) => $c['user_id'] == $_SESSION['user_id']
     ));

     if ($is_collaborator || $_SESSION['user_id'] == $meeting['creator_id']) {
          $result = $database->deleteBooking($removeOnid, $slotHash);
     
          // delete any uploaded file
          $file_name = UPLOADS_ABSPATH . $meeting['hash'] . '/' . $removeOnid . '_upload' . '.*';
          $file_upload->delete($file_name);
          echo json_encode($result);
          // add email here
          $send_email->notifyRemovedAttendee($removeOnid, $_SESSION['user_firstname'] . ' ' . $_SESSION['user_lastname'], $_SESSION['user_onid'], $meeting['name']);
     }
}
