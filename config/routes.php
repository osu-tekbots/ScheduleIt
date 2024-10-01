<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

$request_uri = str_replace(SITE_DIR, '', $_SERVER['REQUEST_URI']);
$request_queries = explode('?', $request_uri);
$request = $request_queries[0];
$schedule_show = preg_match('/schedule\/[0-9]+$/i', $request);
$meeting_show = preg_match('/meetings\/[0-9]+$/i', $request);
$meeting_edit = preg_match('/meetings\/[0-9]+\/edit$/i', $request);
$meeting_edit_dates = preg_match('/meetings\/[0-9]+\/dates$/i', $request);
$uri = explode('/', $request);

switch ($request) {
    case '/':
    case '':
        require_once ABSPATH . 'views/home/index.php';
        break;
    case '/calendar':
        require_once ABSPATH . 'views/calendar/index.php';
        break;
    case '/adminevents':
        require_once ABSPATH . 'views/admin/events.php';
        break;
    case '/adminusers':
        require_once ABSPATH . 'views/admin/users.php';
        break;
    case '/admininfo':
        require_once ABSPATH . 'views/admin/info.php';
        break;
    case '/schedule/create':
        require_once ABSPATH . 'views/schedule/create.php';
        break;
    case '/schedule/available':
        require_once ABSPATH . 'views/schedule/available.php';
        break;
    case '/schedule/invite':
        require_once ABSPATH . 'views/schedule/invite.php';
        break;
    case '/schedule':
        require_once ABSPATH . 'views/schedule/index.php';
        break;
    case ($schedule_show > 0):
        $schedule_id = $uri[2];
        require_once ABSPATH . 'views/schedule/show.php';
        break;
    case '/login':
        require_once ABSPATH . 'views/home/login.php';
        break;
    case '/logout':
        require_once ABSPATH . 'views/home/logout.php';
        break;
    case '/invite':
        require_once ABSPATH . 'views/invites/show.php';
        break;
    case '/manage':
        require_once ABSPATH . 'views/manage/index.php';
        break;
    case ($meeting_edit > 0):
        $meeting_id = $uri[2];
        require_once ABSPATH . 'views/meetings/edit.php';
        break;
    case ($meeting_edit_dates > 0):
        $meeting_id = $uri[2];
        require_once ABSPATH . 'views/meetings/edit_dates.php';
        break;
    case ($meeting_show > 0):
        $meeting_id = $uri[2];
        require_once ABSPATH . 'views/meetings/show.php';
        break;
    case '/meetings/create':
        require_once ABSPATH . 'views/meetings/create.php';
        break;
    case '/meetings/invite':
        require_once ABSPATH . 'views/meetings/invite.php';
        break;
    case '/meetings':
        require_once ABSPATH . 'views/meetings/index.php';
        break;
    case '/profile':
        require_once ABSPATH . 'views/profile/index.php';
        break;
    case '/meetings/remove_attendee':
        require_once ABSPATH . 'views/meetings/remove_attendee.php';
        break;
    default:
        require_once ABSPATH . 'views/errors/error_logged_out.php';
        break;
}
