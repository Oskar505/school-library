<?php
    session_start();

    if (isset($_SESSION['userLoggedIn']) || isset($_SESSION['loggedin'])) {
        $firstName = $_SESSION['firstName'];
    }

    else {
        $firstName = '';
    }


    echo $firstName;
?>