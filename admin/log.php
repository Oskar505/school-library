<?php
    session_start();

    if (!isset($_SESSION['loggedin'])) {
        header('Location: login.php');
        exit;
    }



    function writeToLog($message) {
        $logFile = '/var/log/libraryLog.txt';

        // Otevření souboru pro zápis (s režimem 'a' pro přidání zprávy na konec souboru)
        $fileHandle = fopen($logFile, 'a');

        // Zápis zprávy do logovacího souboru
        fwrite($fileHandle, $message . PHP_EOL);

        // Uzavření souboru
        fclose($fileHandle);
    }
?>