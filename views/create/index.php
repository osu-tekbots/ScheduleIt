<?php

require_once ABSPATH . 'config/session.php';

echo $twig->render('create/index.twig', [
    'title' => "Meeting or Find-A-Time?"
]);