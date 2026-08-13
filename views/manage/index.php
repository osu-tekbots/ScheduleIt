<?php

require_once ABSPATH . 'config/session.php';

$search_term = !empty($_GET['q']) ? $_GET['q'] : '';
$meeting_results = $database->getManageMeetings($_SESSION['user_id'], $search_term);
$find_a_times = $database->getManageSchedules($_SESSION['user_id'], $search_term);
$meetings = [];

// Add dates to meetings
foreach ($meeting_results as $key => $meeting) {
    if ($meeting['id']) {
        $meeting['dates'] = $database->getDatesByMeetingId($meeting['id'], $_SESSION['user_timezone']);
        $meeting['dates_count'] = count($meeting['dates']);
    }

    array_push($meetings, $meeting);
}

echo $twig->render('manage/index.twig', [
    'manage_page' => true,
    'meetings' => $meetings,
    'find_a_times' => $find_a_times,
    'search_result_count' => count($meetings) + count($find_a_times),
    'search_term' => $search_term,
    'title' => 'Manage Created',
]);
