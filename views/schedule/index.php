<?php

require_once ABSPATH . 'config/session.php';

$created_schedules = $database->getUpcomingSchedulesByCreator($_SESSION['user_id']);



echo $twig->render('schedule/index.twig', [
    'title' => 'My Schedules',
    'schedules' => $created_schedules
]);