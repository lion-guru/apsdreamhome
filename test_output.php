<?php
require_once __DIR__ . '/public/index.php';
// The above will run the whole app. We just want to see what happens when we call index().
// Actually, App::run() calls $response->send() which might exit.
