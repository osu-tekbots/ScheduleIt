<?php

require_once ABSPATH . 'config/session.php';
require_once ABSPATH . 'lib/file_upload.php';

$admins = $database->getAdmins();
$isadmin = 0;
if ($_SESSION['is_admin'])
    $isadmin = 1;

echo $twig->render('admin/info.twig', [
    'title' => 'Admininster',
    'id' => $_SESSION['user_id'],
    'isadmin' => $isadmin
]);
