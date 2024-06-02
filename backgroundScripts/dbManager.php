<?php
    // TODO: dodelat kontrolo rezervaci a pujceni mezi tabulkama, kdyz to neni stejny tak asi upozornit admina, asi nejakym mailem kterej by to mohl shrnout co tenhle skript upravoval nebo to zapsat do logu
    // TODO: udelat funkci ktera napise poznamku uzivateli kdyz nevrati knihu vcas
    // TODO: domluvit maily


    session_start();

    if (!isset($_SESSION['loggedin'])) {
        header('Location: /login.php');
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


    // CANCEL OLD RESERVATIONS
    $sql = "SELECT id, reservation, reservationExpiration, name FROM books";
    $result = mysqli_query($conn, $sql);

    if ($result === false) {
            $output[] = 'Error: '.mysqli_error($conn);
    }

    $data = mysqli_fetch_all($result, MYSQLI_ASSOC);


    for ($i = 0; $i < count($data); $i++) {
        $today = new DateTime();
        $today->setTime(0, 0, 0);

        $reservationExpiration = $data[$i]['reservationExpiration'];
        $bookName = $data[$i]['name'];


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
                    $output[] = 'Error: '.mysqli_error($conn);
                }


                // users table, update reserved list
                $sql = "SELECT reserved FROM users WHERE login='$userLogin'";
                $result = mysqli_query($conn, $sql);

                if ($result === false) {
                    $output[] = 'Error: '.mysqli_error($conn);
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
                    $output[] = 'Error: '.mysqli_error($conn);
                }


                // mail
                $mail = new SendMail($userLogin);
                $mail->reservationCanceled($bookName);
            }

            

            // REMIND

            else if ($reservationExpirationDate->modify('-1 day') == $today) {
                $mailReservationExpiration = date_format($reservationExpirationDate, "j. n.");

                $mail = new SendMail($userLogin);
                $mail->reservationReminder($bookName, $mailReservationExpiration);
            }
        }
    }




    // CONTROL DATA BETWEEN TABLES
    // Book data
    $sql = "SELECT id, name, lentTo, lendDate, returnDate, history, reservation, reservationExpiration FROM books";
    $result = mysqli_query($conn, $sql);

    if ($result === false) {
        $output[] = 'Error: '.mysqli_error($conn);
    }

    $booksData = mysqli_fetch_all($result, MYSQLI_ASSOC);

    //control data

    foreach ($booksData as $row) {
        $id = $row['id'];
        $bookName = $row['name'];
        $lentTo = $row['lentTo'];
        $lendDate = $row['lendDate'];
        $returnDate = $row['returnDate'];
        $history = $row['history'];
        $reservation = $row['reservation'];
        $reservationExpiration = $row['reservationExpiration'];


        /// borrowed
        if ($lentTo != '' && $lentTo != null) { // lentTo is not empty
            // nejsou to nijak dulezity chyby
            // if ($lendDate == '' || $lendDate == null) { // lendDate is empty
            //     $output[] = "Books table error: LendDate is empty, ID: $id <br>";
            // }

            // if ($returnDate == '' || $returnDate == null) {
            //     $output[] = "Books table error: ReturnDate is empty, ID: $id <br>";
            // }


            // history
            $historyList = [];

            if ($history != null && $history != '') {
                $historyList = explode(',', $history);
            }

            if (!in_array($lentTo, $historyList)) {
                $output[] = "Books table error: User is not in history, ID: $id <br>";
            }
        }


        elseif ($lendDate != '' && $lendDate != null) { // lendDate is not empty but lentTo is
            $output[] = "Books table error: LentTo is empty, ID: $id <br>";
        }

        elseif ($returnDate != '' && $returnDate != null) { // returnDate is not empty but lentTo is
            $output[] = "Books table error: LentTo is empty, ID: $id <br>";
        }


        // Write note if user didn't returned book in time
        if ($returnDate != null && $returnDate != '') {
            $returnDateTest = strtotime($returnDate);

            if ($returnDateTest === false) { // invalid date
                $output[] = "Books table error: return date is not valid date, ID: $id <br>";
            }

            else { // valid date
                $returnDateObj = new DateTime($returnDate);
                $now = new DateTime();


                // nevraceno 1 den
                if ($returnDateObj->modify('+1 day') == $now) {
                    // ($returnDateObj < $now) 
                    // book was not returned in time

                    $sql = "SELECT note FROM users WHERE login='$lentTo'";
                    $result = mysqli_query($conn, $sql);

                    if ($result === false) {
                        $output[] = 'Error: '.mysqli_error($conn);
                    }


                    // SEND MAIL
                    $mail = new SendMail($lentTo);
                    $mail->returnReminder($bookName, date_format($returnDateObj, "j. n. Y"), true);
                    $output[] = "Nevracena kniha (1 den) id knihy: $id, $bookName, uzivatel: $lentTo";


                    $note = mysqli_fetch_all($result, MYSQLI_ASSOC)[0]['note'];


                    if (!str_contains($note, 'Nevrátil knihu včas')) {
                        $sql = "UPDATE users SET note = CONCAT(note, ' Nevrátil knihu včas') WHERE login = '$lentTo'";
                        $result = mysqli_query($conn, $sql);

                        if ($result === false) {
                            $output[] = 'Error: '.mysqli_error($conn);
                        }
                    }
                }
            }
        }


        ///reserved

        if ($reservation != '' && $reservation != null) { // reservation is not empty
            if ($reservationExpiration == '' || $reservationExpiration == null) { // reservationExpiration is empty
                $output[] = "Books table error: reservationExpiration is empty, ID: $id <br>";
            }
        }

        elseif ($reservationExpiration != '' && $reservationExpiration != null) {
            $output[] = "Books table error: reservation is empty, ID: $id <br>";
        }
    }



    // Users data
    $sql = "SELECT id, borrowed, reserved, borrowedHistory FROM users";
    $result = mysqli_query($conn, $sql);

    if ($result === false) {
        $output[] = 'Error: '.mysqli_error($conn);
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
            $borrowedList = explode(',', $borrowed);

            foreach ($borrowedList as $borrowedBook) {
                if (!in_array($borrowedBook, $historyList)) {
                    $output[] = "Users table error: Borrowed book is not saved in borrowedHistory, user ID: $id, book ID: $borrowedBook <br>";
                }
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
                $output[] = 'Error: '.mysqli_error($conn);
            }

            $user = mysqli_fetch_all($result, MYSQLI_ASSOC);


            // check if user exists in db
            if (empty($user)) {
                $output[] = "Books table error: user $lentTo does not exist in users table (lentTo), book ID: $id <br>";
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
                $output[] = "Mismatched tables error: book is borrowed in books but not in users. User login = $lentTo, book ID: $id <br>";
                // correct
            //     $login = $lentTo;
            //     mysqli_real_escape_string($conn, $login);
                

            //     // get data to update
            //     $sql = "SELECT borrowed, reserved, borrowedHistory FROM users WHERE login = '$login'";
            //     $result = mysqli_query($conn, $sql);
                
            //     if ($result === false) {
            //         showError('Chyba databáze', 'Nastala chyba čtení dat z databáze, zkuste to prosím později.');
            //     }
            
            //     $data = mysqli_fetch_all($result, MYSQLI_ASSOC);


            //     $bookId = $id;

            //     $borrowed = $data[0]['borrowed'];
            //     $borrowedHistory = $data[0]['borrowedHistory'];

            //     // get arrays
            //     !empty($borrowed) ? $borrowed = explode(',', $borrowed): $borrowed = [];
            //     !empty($borrowedHistory) ? $borrowedHistory = explode(',', $borrowedHistory): $borrowedHistory = [];


            //     // bookId is not in borrowed - borrow book
            //     // borrowed
            //     array_push($borrowed, $bookId); // add new book id

            //     // borrowed history
            //     array_push($borrowedHistory, $bookId);
        


            //     // array to string
            //     $borrowed = implode(',', $borrowed);
            //     $borrowedHistory = implode(',', $borrowedHistory);

            //     // update users
            //     $stmt = mysqli_prepare($conn, "UPDATE users SET borrowed = ?, borrowedHistory = ? WHERE login=?");
            //     mysqli_stmt_bind_param($stmt, "sss", $borrowed, $borrowedHistory, $login);
            //     mysqli_stmt_execute($stmt);
            //     mysqli_stmt_close($stmt);


            //     // send mail

            //     $mailReturnDate = date_create($returnDate);
            //     $mailReturnDate = date_format($mailReturnDate, "j. n. Y");

            //     if ($login == 'x6utvrdoc') {
            //         $mail = new SendMail($login);
            //         $mail->bookLent($name, $mailReturnDate);
            //     }
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
                $sql = "SELECT id, lentTo FROM books WHERE id='$bookIdUsers'";
                $result = mysqli_query($conn, $sql);

                if ($result === false) {
                    $output[] = 'Error: '.mysqli_error($conn);
                }

                $book = mysqli_fetch_all($result, MYSQLI_ASSOC)[0];

                $bookIdBooks = $book['id'];

                if ($bookIdUsers != $bookIdBooks) {
                    $output[] = "Mismatched tables error: book is borrowed in users but not in books. User login = $lentTo, Book id = $bookIdBooks <br>";
                }
            }
        }


        if ($reserved != '' && $reserved != null) { // reserved
            $reserved = explode(',', $reserved);

            foreach($reserved as $bookIdUsers) {
                $sql = "SELECT id, reservation FROM books WHERE id='$bookIdUsers'";
                $result = mysqli_query($conn, $sql);

                if ($result === false) {
                    $output[] = 'Error: '.mysqli_error($conn);
                }

                $book = mysqli_fetch_all($result, MYSQLI_ASSOC);

                // check if book exists in db
                if (empty($book) || $book == '') {
                    $output[] = "Users table error: book with id: $bookIdUsers does not exist in books table (reservation), user ID: $id <br>";
                    continue; // skip
                }

                else {
                    $book = $book[0];
                }


                $bookIdBooks = $book['id'];

                if ($bookIdUsers != $bookIdBooks) {
                    $output[] = "Mismatched tables error: book is reserved in users but not in books. User login = $lentTo, Book id = $bookIdBooks <br>";
                }
            }
        }
    }





    // EXTEND RESERVATION IF BOOK WASN'T RETURNED IN TIME

    $sql = "SELECT id, reservation, reservationExpiration FROM books WHERE STR_TO_DATE(returnDate, '%Y-%m-%d') < CURDATE() AND reservation IS NOT NULL AND reservation != ''";
    $result = mysqli_query($conn, $sql);

    if ($result === false) {
        $output[] = 'Error: '.mysqli_error($conn);
    }

    $books = mysqli_fetch_all($result, MYSQLI_ASSOC);


    foreach ($books as $book) {
        $bookId = $book['id'];
        $reservation = $book['reservation'];

        $today = new DateTime();
        $today->add(new DateInterval('P3D'));
        $reservationExpiration = $today->format('Y-m-d');

        //TODO: send email

        $sql = "UPDATE books SET reservationExpiration='$reservationExpiration' WHERE id='$bookId'";
        $result = mysqli_query($conn, $sql);

        if ($result === false) {
            $output[] = 'Error: '.mysqli_error($conn);
        }

        $output[] = "Extended reservation: book: $id, user: $reservation.";
    }



    print_r($output);


    // SEND MAIL
    $mail = new SendMail('knihovna');
    $mail->cronJobOutput('dbManager', $output);
    $mail->sendBackup();
    



    $conn->close();
?>