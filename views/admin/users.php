<?php

require_once ABSPATH . 'config/session.php';
require_once ABSPATH . 'lib/file_upload.php';

$isadmin = 0;
if ($_SESSION['is_admin'])
    $isadmin = 1;

$search_term = !empty($_GET['q']) ? $_GET['q'] : '';
$users = $database->getAllUsersBySearchTerm($search_term);

foreach ($users as $key => $user) {
    $isadmin = $database->getAdminByUserId($user['id']);
    if ($isadmin == null) {
        $users[$key]['isAdmin'] = false;
    } else {
        $users[$key]['isAdmin'] = true;
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($_SESSION['is_admin']) {
        if (isset($_POST['userId']) && isset($_POST['makeAdmin'])) {
            $database->addAdmin($_POST['userId']);
        }
    }
}

echo $twig->render('admin/users.twig', [
    'title' => 'Admininster',
    'id' => $_SESSION['user_id'],
    'isadmin' => $isadmin,
    'users' => $users
]);
