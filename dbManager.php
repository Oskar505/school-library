<?php
    // TODO: dodelat kontrolo rezervaci a pujceni mezi tabulkama, kdyz to neni stejny tak asi upozornit admina, asi nejakym mailem kterej by to mohl shrnout co tenhle skript upravoval nebo to zapsat do logu
    // TODO: udelat funkci ktera napise poznamku uzivateli kdyz nevrati knihu vcas
    // TODO: domluvit maily

    // get secrets
    require('/var/secrets.php');

    $sqlUser = $secrets['sql-user'];
    $sqlPassword = $secrets['sql-password'];
    $database = $secrets['sql-database'];


    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);



    $conn = mysqli_connect('localhost', $sqlUser, $sqlPassword, $database);

    if (!$conn) {
        echo 'chyba pripojeni'.mysqli_connect_error();
    }


    // CANCEL OLD RESERVATIONS
    $sql = "SELECT id, reservation, reservationExpiration FROM books";
    $result = mysqli_query($conn, $sql);

    if ($result === false) {
            echo 'Error: '.mysqli_error($conn);
    }

    $data = mysqli_fetch_all($result, MYSQLI_ASSOC);


    for ($i = 0; $i < count($data); $i++) {
        $today = new DateTime();
        $today->setTime(0, 0, 0);

        $reservationExpiration = $data[$i]['reservationExpiration'];


        if ($reservationExpiration != '0' && $reservationExpiration != '') { // book is reserved
            $reservationExpirationDate = new DateTime($data[$i]['reservationExpiration']);
            $reservationExpirationDate->setTime(0, 0, 0);

            $id = mysqli_real_escape_string($conn, $data[$i]['id']);
            $userLogin = mysqli_real_escape_string($conn, $data[$i]['reservation']);


            if ($reservationExpirationDate < $today) { // cancel reservation
                // books table
                $sql = "UPDATE books SET reservation='', reservationExpiration='' WHERE id='$id'";
                $result = mysqli_query($conn, $sql);

                if ($result === false) {
                        echo 'Error: '.mysqli_error($conn);
                }


                // users table, update reserved list
                $sql = "SELECT reserved FROM users WHERE login='$userLogin'";
                $result = mysqli_query($conn, $sql);

                if ($result === false) {
                        echo 'Error: '.mysqli_error($conn);
                }

                $reservedBooks = mysqli_fetch_all($result, MYSQLI_ASSOC)[0]['reserved'];
                $reservedBooks = explode(',', $reservedBooks);

                $index = array_search($id, $reservedBooks, true); // get index
                if ($index !== false) {
                    unset($reservedBooks[$index]); // delete
                    $reservedBooks = implode(',', $reservedBooks);
                }

                $sql = "UPDATE users SET reserved='$reservedBooks' WHERE login='$userLogin'";
                $result = mysqli_query($conn, $sql);

                if ($result === false) {
                    echo 'Error: '.mysqli_error($conn);
                }
            }
        }
    }




    // CONTROL DATA BETWEEN TABLES
    // Book data
    $sql = "SELECT id, lentTo, lendDate, returnDate, history, reservation, reservationExpiration FROM books";
    $result = mysqli_query($conn, $sql);

    if ($result === false) {
            echo 'Error: '.mysqli_error($conn);
    }

    $booksData = mysqli_fetch_all($result, MYSQLI_ASSOC);

    //control data

    foreach ($booksData as $row) {
        $id = $row['id'];
        $lentTo = $row['lentTo'];
        $lendDate = $row['lendDate'];
        $returnDate = $row['returnDate'];
        $history = $row['history'];
        $reservation = $row['reservation'];
        $reservationExpiration = $row['reservationExpiration'];

        //echo "lendDate $lendDate <br>";


        /// borrowed
        if ($lentTo != '' && $lentTo != null) { // lentTo is not empty
            if ($lendDate == '' || $lendDate == null) { // lendDate is empty
                echo "Books table error: LendDate is empty, ID: $id <br>";
            }

            if ($returnDate == '' || $returnDate == null) {
                echo "Books table error: ReturnDate is empty, ID: $id <br>";
            }


            // history
            $historyList = [];

            if ($history != null && $history != '') {
                $historyList = explode(',', $history);
            }

            if (!in_array($lentTo, $historyList)) {
                echo "Books table error: User is not in history, ID: $id <br>";
            }
        }


        elseif ($lendDate != '' && $lendDate != null) { // lendDate is not empty but lentTo is
            echo "Books table error: LentTo is empty, ID: $id <br>";
        }

        elseif ($returnDate != '' && $returnDate != null) { // returnDate is not empty but lentTo is
            echo "Books table error: LentTo is empty, ID: $id <br>";
        }


        // Write note if user didn't returned book in time
        if ($returnDate != null && $returnDate != '') {
            $returnDateTest = strtotime($returnDate);

            if ($returnDateTest === false) { // invalid date
                echo "Books table error: return date is not valid date, ID: $id <br>";
            }

            else { // valid date
                $returnDateObj = new DateTime($returnDate);
                $now = new DateTime();


                if ($returnDateObj < $now) {
                    // book was not returned in time

                    $sql = "SELECT note FROM users WHERE login='$lentTo'";
                    $result = mysqli_query($conn, $sql);

                    if ($result === false) {
                        echo 'Error: '.mysqli_error($conn);
                    }

                    $note = mysqli_fetch_all($result, MYSQLI_ASSOC)[0]['note'];


                    if (!str_contains($note, 'Nevrátil knihu včas')) {
                        $sql = "UPDATE users SET note = CONCAT(note, ' Nevrátil knihu včas') WHERE login = '$lentTo'";
                        $result = mysqli_query($conn, $sql);

                        if ($result === false) {
                            echo 'Error: '.mysqli_error($conn);
                        }
                    }
                }
            }
        }


        ///reserved

        if ($reservation != '' && $reservation != null) { // reservation is not empty
            if ($reservationExpiration == '' || $reservationExpiration == null) { // reservationExpiration is empty
                echo "Books table error: reservationExpiration is empty, ID: $id <br>";
            }
        }

        elseif ($reservationExpiration != '' && $reservationExpiration != null) {
            echo "Books table error: reservation is empty, ID: $id <br>";
        }
    }



    // Users data
    $sql = "SELECT id, borrowed, reserved, borrowedHistory FROM users";
    $result = mysqli_query($conn, $sql);

    if ($result === false) {
            echo 'Error: '.mysqli_error($conn);
    }

    $usersData = mysqli_fetch_all($result, MYSQLI_ASSOC);


    // control
    foreach ($usersData as $row) {
        $id = $row['id'];
        $borrowed = $row['borrowed'];
        $reserved = $row['reserved'];
        $borrowedHistory = $row['borrowedHistory'];


        if ($borrowed != '' && $borrowed != null) { // borrowed is not empty
            $historyList = explode(',', $borrowedHistory);

            if (!in_array($borrowed, $historyList)) {
                echo "Users table error: Book is not in borrowedHistory, ID: $id <br>";
            }
        }
    }
    




    // Mismatched data between tables

    // books
    foreach ($booksData as $row) { // borrowed
        $id = $row['id'];
        $lentTo = $row['lentTo'];
        $lendDate = $row['lendDate'];
        $returnDate = $row['returnDate'];
        $history = $row['history'];
        $reservation = $row['reservation'];
        $reservationExpiration = $row['reservationExpiration'];


        if ($lentTo != '' && $lentTo != null) {
            $sql = "SELECT login, borrowed FROM users WHERE login='$lentTo'";
            $result = mysqli_query($conn, $sql);

            if ($result === false) {
                echo 'Error: '.mysqli_error($conn);
            }

            $user = mysqli_fetch_all($result, MYSQLI_ASSOC);


            // check if user exists in db
            if (empty($user)) {
                echo "Books table error: user $lentTo does not exist in users table (lentTo), book ID: $id <br>";
                continue; // skip
            }
            
            else {
                $user = $user[0];
            }



            // borrowed string to list
            $borrowed = $user['borrowed'];

            if ($borrowed != null && $borrowed != '') {
                $borrowed = explode(',', $borrowed);
            }

            else {
                $borrowed = [];
            }
            

            if (!in_array($id, $borrowed)) {
                echo "Mismatched tables error: book is borrowed in books but not in users. User login = $lentTo, book ID: $id <br>";
            }
        }


        if ($reservation != '' && $reservation != null) { // reserved
            $sql = "SELECT login, reserved FROM users WHERE login='$reservation'";
            $result = mysqli_query($conn, $sql);

            if ($result === false) {
                echo 'Error: '.mysqli_error($conn);
            }

            $user = mysqli_fetch_all($result, MYSQLI_ASSOC)[0];

            $reserved = $user['reserved'];
            $reserved = explode(',', $reserved);

            if (!in_array($id, $reserved)) {
                echo "Mismatched tables error: book is reserved in books but not in users. User login = $reservation <br>";
            }
        }
    }



    // users
    foreach ($usersData as $row) { // borrowed
        $id = $row['id'];
        $borrowed = $row['borrowed'];
        $reserved = $row['reserved'];
        $borrowedHistory = $row['borrowedHistory'];


        if ($borrowed != '' && $borrowed != null) {
            $borrowed = explode(',', $borrowed);

            foreach($borrowed as $bookIdUsers) {
                $sql = "SELECT lentTo FROM books WHERE id='$bookIdUsers'";
                $result = mysqli_query($conn, $sql);

                if ($result === false) {
                    echo 'Error: '.mysqli_error($conn);
                }

                $book = mysqli_fetch_all($result, MYSQLI_ASSOC)[0];

                $bookIdBooks = $book['id'];

                if ($bookIdUsers != $bookIdBooks) {
                    echo "Mismatched tables error: book is borrowed in users but not in books. User login = $lentTo, Book id = $bookIdBooks <br>";
                }
            }
        }


        if ($reserved != '' && $reserved != null) { // reserved
            $reserved = explode(',', $reserved);

            foreach($reserved as $bookIdUsers) {
                $sql = "SELECT reservation FROM books WHERE id='$bookIdUsers'";
                $result = mysqli_query($conn, $sql);

                if ($result === false) {
                    echo 'Error: '.mysqli_error($conn);
                }

                $book = mysqli_fetch_all($result, MYSQLI_ASSOC);

                // check if book exists in db
                if (empty($book) || $book == '') {
                    echo "Users table error: book with id: $bookIdUsers does not exist in books table (reservation), user ID: $id <br>";
                    continue; // skip
                }

                else {
                    $book = $book[0];
                }


                $bookIdBooks = $book['id'];

                if ($bookIdUsers != $bookIdBooks) {
                    echo "Mismatched tables error: book is reserved in users but not in books. User login = $lentTo, Book id = $bookIdBooks <br>";
                }
            }
        }
    }

    /*

    $sql = "SELECT *
        FROM users
        LEFT JOIN books ON users.login = books.lentTo AND knihy.id_knihy = $id_knihy
        WHERE uzivatele.id_uzivatele = ";

    $result = $conn->query($sql);
    */

    $conn->close();
?>