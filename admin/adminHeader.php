<?php
/*
    session_start();

    if (!isset($_SESSION['loggedin'])) {
        header('Location: /userLogin.php');
        exit;
    }
    */
?>


<header class='header'>
    <h1 class='h1 adminH1'><?php echo $h1 ?></h1>

    <a class='logoutBtn' href="logout.php">Odhlásit se</a>
</header>