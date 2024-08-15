<?php

require_once ABSPATH . 'config/session.php';
require_once ABSPATH . 'lib/file_upload.php';

$admins = $database->getAdmins();
$isadmin = 0;
if ($_SESSION['is_admin'])
    $isadmin = 1;

$orphanCounters = array();
$sql = array();
$orphanUploads = array();


$uploads = $file_upload->getAllUploadFolders();
$count = 0;
foreach ($uploads as $key => $upload) {
    $event = $database->getMeetingByHash($upload);
    if (!in_array($upload, array('.gitkeep', '.', '..'))) {
        if ($event == null) {
            $count++;
            array_push($orphanUploads, $upload);
        } 
    }
}
$orphanCounters['uploads'] = $count;

$orphanedInvites = $database->getOrphanedBookings();
$orphanCounters['invites'] = count($orphanedInvites);
if ($orphanCounters['invites'] != 0) {
    $sql['invites'] = "SELECT * FROM meb_invites WHERE fk_event_id NOT IN (SELECT id FROM meb_event);";
}

$orphanedTimeslots = $database->getOrphanedTimeslots();
$orphanCounters['timeslots'] = count($orphanedTimeslots);
if ($orphanCounters['timeslots'] != 0) {
    $sql['timeslots'] = "SELECT * FROM meb_timeslot WHERE fk_event_id NOT IN (SELECT id FROM meb_event);";
}

$orphanedBookings = $database->getOrphanedBookings();
$orphanCounters['bookings'] = count($orphanedBookings);
if ($orphanCounters['bookings'] != 0) {
    $sql['bookings'] = "SELECT * FROM meb_booking WHERE fk_timeslot_id NOT IN (SELECT id FROM meb_timeslot);";
}

echo $twig->render('admin/info.twig', [
    'title' => 'Admininster',
    'id' => $_SESSION['user_id'],
    'isadmin' => $isadmin,
    'orphanCounters' => $orphanCounters,
    'sql' => $sql,
    'orphanUploads' => $orphanUploads
]);
