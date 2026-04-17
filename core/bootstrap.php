<?php

// Require core files in App.php so index.php doesn't have to require them individually
$core_files = [
    CORE_DIR . '/Controller.php',
    CORE_DIR . '/Database.php', // We will create this
    APP_DIR . '/Helpers/Security.php', // We will create this
    APP_DIR . '/Helpers/Session.php', // We will create this
];

foreach ($core_files as $file) {
    if (file_exists($file)) {
        require_once $file;
    }
}
