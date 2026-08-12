<?php

require_once ABSPATH . 'config/session.php';

$isadmin = 0;
if ($_SESSION['is_admin'])
    $isadmin = 1;

$search_term = !empty($_GET['q']) ? $_GET['q'] : '';
$events = $database->getAllFindATimesBySearchTerm($search_term);

$oldEvents = array();
foreach ($events as $key => $event) {
    if ($database->getFindATimeIsOld($event['id'])) {
        $events[$key]['isOld'] = true;
        array_push($oldEvents, $event);
    } else {
        $events[$key]['isOld'] = false;
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($_SESSION['is_admin']) {
        if (isset($_POST['eventId']) && isset($_POST['deleteEvent'])) {
            $database->deleteFindATime($_POST['eventId']);
            header("Refresh: 1");
        }
        if (isset($_POST['deleteAllOldEvents'])) {
            foreach ($oldEvents as $oldEvent) {
                $database->deleteFindATime($oldEvent['id']);
            }
            header("Refresh: 1");
        }
    }
}

echo $twig->render('admin/findatimes.twig', [
    'title' => 'Admininster',
    'id' => $_SESSION['user_id'],
    'isadmin' => $isadmin,
    'events' => $events
]);
