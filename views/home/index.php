<?php

require_once ABSPATH . 'config/database.php';

$siteStats = $database->getSiteStatistics();

echo $twig->render('home/index.twig', [
    'title' => 'home',
    'stats' => $siteStats
]);
