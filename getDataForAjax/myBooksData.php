<?php
    // get secrets
    require('/var/secrets.php');

    $sqlUser = $secrets['sql-user'];
    $sqlPassword = $secrets['sql-password'];
    $database = $secrets['sql-database'];


    include 'functions.php';

    session_start();






    if (isset($_SESSION['userLoggedIn'])) {
        $login = $_SESSION['login'];


        // get data from mysql
        try {
            $conn = mysqli_connect('localhost', $sqlUser, $sqlPassword, $database);
        }
        
        catch (mysqli_sql_exception $e) {
            showError('Chyba připojení', 'Nastala chyba připojení k databázi, zkuste to prosím později.');
        }

        mysqli_real_escape_string($conn, $login);


        $sql = "SELECT borrowed, reserved FROM users WHERE login='$login'";
        $result = mysqli_query($conn, $sql);

        if ($result === false) {
            showError('Chyba databáze', 'Nastala chyba čtení dat z databáze, zkuste to prosím později.');
        }

        $data = mysqli_fetch_all($result, MYSQLI_ASSOC);


        $reserved = explode(',', $data[0]['reserved']);
        $borrowed = explode(',', $data[0]['borrowed']);

        $reservedBooksData = [];
        $borrowedBooksData = [];


        // get reserved books data
        if (!empty($reserved)) {
            foreach ($reserved as $bookId) {
                $sql = "SELECT name, reservationExpiration FROM books WHERE id='$bookId'";
                $result = mysqli_query($conn, $sql);

                if ($result === false) {
                    showError('Chyba databáze', 'Nastala chyba čtení dat z databáze, zkuste to prosím později.');
                }

                $data = mysqli_fetch_all($result, MYSQLI_ASSOC);

                $bookData = ['id' => $bookId, 'name' => $data[0]['name'], 'endDate' => $data[0]['reservationExpiration']];
                array_push($reservedBooksData, $bookData);
            }
        }
        


        // get borrowed books data
        if (!empty($borrowed)) {
            foreach ($borrowed as $bookId) {
                $sql = "SELECT name, returnDate FROM books WHERE id='$bookId'";
                $result = mysqli_query($conn, $sql);

                if ($result === false) {
                    showError('Chyba databáze', 'Nastala chyba čtení dat z databáze, zkuste to prosím později.');
                }

                $data = mysqli_fetch_all($result, MYSQLI_ASSOC);

                $bookData = ['id' => $bookId, 'name' => $data[0]['name'], 'endDate' => $data[0]['returnDate']];
                array_push($borrowedBooksData, $bookData);
            }
        }


        // return data
        echo json_encode([$reservedBooksData, $borrowedBooksData]);
    }




    else {
        echo json_encode(['not logged in']);
    }
?>