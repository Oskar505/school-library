<?php
    session_start();

    if (!isset($_SESSION['loggedin'])) {
        header('Location: /userLogin.php');
        exit;
    }


    // get secrets
    require('/var/secrets.php');

    $sqlUser = $secrets['sql-user'];
    $sqlPassword = $secrets['sql-password'];
    $database = $secrets['sql-database'];


    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    include_once('../sendMail.php');


    // output of this script, this will be sent in mail
    $output = [];



    $conn = mysqli_connect('localhost', $sqlUser, $sqlPassword, $database);

    if (!$conn) {
        $output[] = 'chyba pripojeni'.mysqli_connect_error();
    }


    // GET BOOKS
    $sql = "SELECT id, name, lentTo, returnDate FROM books WHERE CAST(returnDate AS DATE) < CURRENT_DATE";
    $result = mysqli_query($conn, $sql);

    if ($result === false) {
        $output[] = 'Error: '.mysqli_error($conn);
    }

    $data = mysqli_fetch_all($result, MYSQLI_ASSOC);

    print_r($data);

    foreach ($data as $book) {
        $userLogin = $book['lentTo'];
        $returnDate = $book['returnDate'];
        $returnDateObj = new DateTime($returnDate);
        $bookId = $book['id'];
        $bookName = $book['name'];
        
        $mail = new SendMail($userLogin);
        $mail->returnReminder($bookName, date_format($returnDateObj, "j. n. Y"), true);
        
        $output[] = "Nevracena kniha id knihy: $bookId, $bookName, uzivatel: $userLogin";
    }



    // SEND MAIL
    $mail = new SendMail('knihovna');
    $mail->cronJobOutput('returnReminder', $output);

    $conn->close();