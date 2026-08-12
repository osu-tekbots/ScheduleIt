<?php

require_once ABSPATH . 'config/session.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $timezone = $_POST['timezone'];

    if (! in_array($timezone, DateTimeZone::listIdentifiers())) {
        $msg->error('Invalid timezone; please select a different one.');
    } else {
        $success = $database->updateUserTimezone($_SESSION['user_id'], $timezone);

        if ($success) {
            $msg->success('Timezone updated.');
            
            $_SESSION['user_timezone'] = $timezone;
            $twig->addGlobal('user_timezone', $_SESSION['user_timezone']);
        } else {
            $msg->error('Failed to update timezone.');
        }
    }
}

echo $twig->render('profile/index.twig', [
    'profile_page' => true,
    'title' => 'Profile',
]);
