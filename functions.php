<?php
    function showError($error, $errorMessage, $link = '/index.php') {
        session_start();

        $_SESSION['error'] = $error;
        $_SESSION['errorMessage'] = $errorMessage;
        $_SESSION['link'] = $link;

        header("Location: /error.php");
        exit();
    }
?>