<?php

require_once ABSPATH . 'config/session.php';

$search_term = !empty($_GET['q']) ? $_GET['q'] : null;

$upcoming_schedules = $database->getAllUpcomingSchedules($_SESSION['user_id']);
$created_schedules = $database->getUpcomingSchedulesByCreator($_SESSION['user_id']);
$past_schedules = $database->getPastSchedules($_SESSION['user_id']);
$search_schedules = $database->getSchedulesBySearchTerm($_SESSION['user_id'], $search_term);

$schedule_types = [ &$upcoming_schedules, &$created_schedules, &$past_schedules, &$search_schedules];
foreach ($schedule_types as &$list) {
    foreach ($list as &$schedule) {
        $schedule['top_timeslots'] = $database->getTopScheduleTimes($schedule['id'], 3);
    }
}

echo $twig->render('schedule/index.twig', [
    'title' => 'My Find-a-Times',
    'schedules_page' => true,
    'search_result_count' => count($search_schedules),
    'search_term' => $search_term,
    'upcoming_schedules' => $upcoming_schedules,
    'created_schedules' => $created_schedules,
    'past_schedules' => $past_schedules,
    'search_schedules' => $search_schedules,
]);
