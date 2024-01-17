<?php
    session_start();

    if (!isset($_SESSION['loggedin'])) {
        header('Location: /userLogin.php');
        exit;
    }



    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);


    include '../functions.php';

    // get secrets
    require('/var/secrets.php');

    $sqlUser = $secrets['sql-user'];
    $sqlPassword = $secrets['sql-password'];
    $database = $secrets['sql-database'];


    // mail
    require_once('../sendMail.php');


    if (isset($_POST['add'])) {
        $registration = $_POST['registration'];
        $isbn = $_POST['isbn'];
        $subject = $_POST['subject'];
        $class = $_POST['class'];
        $publisher = $_POST['publisher'];
        $author = $_POST['author'];
        $name = $_POST['name'];
        $price = $_POST['price'];
        $dateAdded = $_POST['dateAdded'];
        $lentTo = $_POST['lentTo'];
        $lendDate = $_POST['lendDate'];
        $returnDate = $_POST['returnDate'];
        $history = '';
        $reservation = $_POST['reservation'];
        $reservationExpiration = '';
        $note = $_POST['note'];
        $discarded = $_POST['discarded'];


        
        $allData = [$registration, $isbn, $subject, $class, $publisher, $author, $name, $price, $dateAdded, $lentTo, $lendDate, $returnDate, $history, $reservation, $reservationExpiration, $note, $discarded];



        try {
            $conn = mysqli_connect('localhost', $sqlUser, $sqlPassword, $database);
        }
        
        catch (mysqli_sql_exception $e) {
            showError('Chyba připojení', "Nastala chyba připojení k databázi, zkuste to prosím později.");
        }

        

        if (!$conn) {
            showError('Chyba databáze', 'Nastala chyba čtení dat z databáze, zkuste to prosím později.');
        }


        $allData = array_map(function($value) use ($conn) {
            return empty($value) ? NULL : mysqli_real_escape_string($conn, $value);
        }, $allData);
        

        // discarded
        if ($allData[16] == 'on') {
            $allData[16] = 1;
        }
        
        else {
            $allData[16] = 0;
        }


        // reservationExpiration
        if (!empty($reservation)) {
            $today = date('Y-m-d');
            $reservationExpiration = date('Y-m-d', strtotime($today . ' +3 days')); // + 3 days
            $allData[13] = $reservationExpiration;
        }


        // delete user info - reservations ...
        $allData[9] = '';
        $allData[10] = '';
        $allData[11] = '';
        $allData[12] = '';
        $allData[13] = '';
        $allData[14] = '';


        
        $stmt = mysqli_prepare($conn, "INSERT INTO books (registration, isbn, subject, class, publisher, author, name, price, dateAdded, lentTo, lendDate, returnDate, history, reservation, reservationExpiration, note, discarded) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        mysqli_stmt_bind_param($stmt, "ssssssssssssssssi", $allData[0], $allData[1], $allData[2], $allData[3], $allData[4], $allData[5], $allData[6], $allData[7], $allData[8], $allData[9], $allData[10], $allData[11], $allData[12], $allData[13], $allData[14], $allData[15], $allData[16]);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        // send form only once
        header('Location: index.php');
        exit;
    }



    // edit
    if (isset($_POST['edit'])) {
        try {
            $conn = mysqli_connect('localhost', $sqlUser, $sqlPassword, $database);
        }
        
        catch (mysqli_sql_exception $e) {
            showError('Chyba připojení', "Nastala chyba připojení k databázi, zkuste to prosím později.");
        }

        if (!$conn) {
            showError('Chyba databáze', 'Nastala chyba čtení dat z databáze, zkuste to prosím později.');
        }




        $lines = $_POST['selectedInputs'];
        $editAll = false;
        $editLentTo = true;
        $editReservation = true;
        $editReservationExpiration = true;
        $editDiscarded = true;
        $bookIdList = [];

        $id = $_POST['id'];
        $registration = $_POST['registration'];
        $isbn = $_POST['isbn'];
        $subject = $_POST['subject'];
        $class = $_POST['class'];
        $publisher = $_POST['publisher'];
        $author = $_POST['author'];
        $name = $_POST['name'];
        $price = $_POST['price'];
        $dateAdded = $_POST['dateAdded'];
        $lentTo = $_POST['lentTo'];
        $lendDate = $_POST['lendDate'];
        $returnDate = $_POST['returnDate'];
        $history = '';
        $reservation = $_POST['reservation'];
        $reservationExpiration = '';
        $note = $_POST['note'];
        $discarded = $_POST['discarded'];
        $oldLentTo = $_POST['oldLentTo'];
        $oldReservation = $_POST['oldReservation'];



        if ($lines != 'editOne') {
            $lines = json_decode($lines);

            if (empty($lines)) { // edit nothing
                header('Location: /admin/index.php');
                exit;
            }

            $editAll = true;

            $editLentTo = in_array('0', $lines);
            $editReservation = in_array('4', $lines);
            $editReservation = in_array('5', $lines);
            $editDiscarded = in_array('16', $lines);
        }


        // pouziti variable variable $$
        $variableNames = ["registration", "isbn", "subject", "class", "publisher", "author", "name", "price", "dateAdded", "lentTo", "lendDate", "returnDate", "history", "reservation", "reservationExpiration", "note", "discarded", "id"];

        // escape strings
        foreach ($variableNames as $variableName) {
            if (isset($$variableName)) { // Kontrola, zda proměnná existuje
                $value = $$variableName; // Získání hodnoty proměnné
                $$variableName = empty($value) ? NULL : mysqli_real_escape_string($conn, $value); // Aktualizace hodnoty proměnné
            }
        }




        // control username
        if (!empty($lentTo) && $editLentTo) {
            $sql = "SELECT COUNT(*) FROM users WHERE login='$lentTo'";
            $result = mysqli_query($conn, $sql);

            if ($result === false) {
                showError('Chyba databáze', 'Nastala chyba čtení dat z databáze, zkuste to prosím později.');
            }

            $usersCount = mysqli_fetch_all($result, MYSQLI_ASSOC)[0]['COUNT(*)'];


            if ($usersCount == 0) {
                showError('Úprava se nezdařila.', "Zadali jste neplatné uživatelské jméno \"$lentTo\" do políčka půjčeno.", '/admin');
            }
        }


        if (!empty($reservation) && $editReservation) {
            $sql = "SELECT COUNT(*) FROM users WHERE login='$reservation'";
            $result = mysqli_query($conn, $sql);

            if ($result === false) {
                showError('Chyba databáze', 'Nastala chyba čtení dat z databáze, zkuste to prosím později.');
            }

            $usersCount = mysqli_fetch_all($result, MYSQLI_ASSOC)[0]['COUNT(*)'];


            if ($usersCount == 0) {
                showError('Úprava se nezdařila.', "Zadali jste neplatné uživatelské jméno \"$reservation\" do políčka rezervace.", '/admin');
            }
        }


        // discarded
        if ($editDiscarded) {
            if ($discarded == 'on') {
                $discarded = 1;
            }
            
            else {
                $discarded = 0;
            }
        }
        


        // reservationExpiration
        if (!empty($reservation) && $editReservationExpiration) {
            $today = date('Y-m-d');
            $reservationExpiration = date('Y-m-d', strtotime($today . ' +3 days')); // + 3 days
        }


        // history
        if ($lentTo != '' && !$editAll) {
            $query = "SELECT history FROM books WHERE id = $id";
            $result = mysqli_query($conn, $query);
            $row = mysqli_fetch_assoc($result);
            $history = $row['history'];


            if (!empty($history)) {
                $history = $history . ', ' . $lentTo;
            }

            else {
                $history = $lentTo;
            }
        }



        // edit all - generate sql

        if ($editAll) {
            // generate sql
            $where = getWhere();

            $columns = ['lentTo', 'class', 'lendDate', 'returnDate', 'reservation', 'reservationExpiration', 'registration', 'isbn', 'subject', 'publisher', 'author', 'name', 'price', 'dateAdded', 'note', '', 'discarded'];
            $dataList = [$lentTo, $class, $lendDate, $returnDate, $reservation, $reservationExpiration, $registration, $isbn, $subject, $publisher, $author, $name, $price, $dateAdded, $note, '', $discarded];
            $set = '';

            if ($editLentTo && empty($lentTo)) {
                $lentTo = null;
                $lendDate = null;
                $class = null;
                $returnDate = null;
            }

            if ($editReservation && empty($reservation)) {
                $reservation = null;
                $reservationExpiration = null;
            }
            

            foreach ($lines as $line) {
                $column = $columns[$line];
                $dataToUpdate = $dataList[$line];

                
                
                // TODO: nastavit to jako null pokud je to prazdny
                if ($set == '') {
                    $set = empty($dataToUpdate) ? "SET $column = NULL" : "SET $column = '$dataToUpdate'";
                }

                else {
                    $set = "$set, $column = '$dataToUpdate'";
                    $set = empty($dataToUpdate) ? "$set, $column = NULL" : "$set, $column = '$dataToUpdate'";
                }
            }

            



            if ($editLentTo || $editReservation) {
                $sql = "SELECT id FROM books $where";
                $result = mysqli_query($conn, $sql);
                
                if ($result === false) {
                    showError('Chyba databáze', 'Nastala chyba čtení dat z databáze, zkuste to prosím později.');
                }
            
                $data = mysqli_fetch_all($result, MYSQLI_ASSOC);
                

                foreach ($data as $book) {
                    array_push($bookIdList, $book['id']);
                }
            }



            $booksSql = "UPDATE books $set $where";
            echo $booksSql;
        }





        // update books jsem presunul dolu protoze update useru muze hazet chyby tak at se to upravi jen kdyz je vse ok


        //UPDATE USERS
        if (($oldLentTo != $lentTo) || ($editAll && $editLentTo)) {
            if (!$editAll) {
                empty($oldLentTo) ? $login = $lentTo : $login = $oldLentTo; // get right login
                mysqli_real_escape_string($conn, $login);
            }

            else {
                $login = $lentTo;
            }
            

            // get data to update
            $sql = "SELECT borrowed, reserved, borrowedHistory FROM users WHERE login = '$login'";
            $result = mysqli_query($conn, $sql);
            
            if ($result === false) {
                showError('Chyba databáze', 'Nastala chyba čtení dat z databáze, zkuste to prosím později.');
            }
        
            $data = mysqli_fetch_all($result, MYSQLI_ASSOC);


            $bookId = $id;

            $borrowed = $data[0]['borrowed'];
            $reserved = $data[0]['reserved'];
            $borrowedHistory = $data[0]['borrowedHistory'];

            // get arrays
            !empty($borrowed) ? $borrowed = explode(',', $borrowed): $borrowed = [];
            !empty($reserved) ? $reserved = explode(',', $reserved): $reserved = [];
            !empty($borrowedHistory) ? $borrowedHistory = explode(',', $borrowedHistory): $borrowedHistory = [];


            // bookId is not in borrowed - borrow book
            if ((empty($oldLentTo) && !empty($lentTo) && !in_array($bookId, $borrowed) && !$editAll) || ($editAll && !empty($lentTo))) {
                if (!$editAll) {
                    // borrowed
                    array_push($borrowed, $bookId); // add new book id

                    // borrowed history
                    array_push($borrowedHistory, $bookId);
                }
                
                else {
                    // return if lent to someone else then borrow to new user
                    returnMultipleBooks($conn, $bookIdList, 'borrowed');

                    foreach ($bookIdList as $bookId) {
                        //borrowed
                        array_push($borrowed, $bookId); // add new book id

                        // borrowed history
                        array_push($borrowedHistory, $bookId);
                    }
                }



                // array to string
                $borrowed = implode(',', $borrowed);
                $borrowedHistory = implode(',', $borrowedHistory);

                // update users
                $stmt = mysqli_prepare($conn, "UPDATE users SET borrowed = ?, borrowedHistory = ? WHERE login=?");
                mysqli_stmt_bind_param($stmt, "sss", $borrowed, $borrowedHistory, $login);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);


                // send mail

                $mailReturnDate = date_create($returnDate);
                $mailReturnDate = date_format($mailReturnDate, "j. n. Y");

                $mail = new SendMail($login);
                $mail->bookLent($name, $mailReturnDate);
            }


            // book is in users - return book
            else if ((!empty($oldLentTo) && empty($lentTo) && !$editAll) || ($editAll && empty($lentTo))) {
                if (!$editAll) {
                    $borrowed = array_diff($borrowed, array($bookId));

                    // array to string
                    $borrowed = implode(',', $borrowed);

                    $dataCount = 1;
                }



                else { // edit all 

                    returnMultipleBooks($conn, $bookIdList, 'borrowed');
                    $dataCount = 1; // test

                    /*
                    foreach ($bookIdList as $bookId) {
                        if (is_string($bookId)) { // ecape if string
                            $bookId = mysqli_real_escape_string($conn, $bookId);
                        }


                        $sql = "SELECT login, borrowed FROM users WHERE borrowed LIKE CONCAT('$bookId', ',', '%') OR borrowed LIKE CONCAT('%', ',', '$bookId') OR borrowed LIKE CONCAT('%', ',$bookId,', '%') OR borrowed = '$bookId'";
                        $result = mysqli_query($conn, $sql);
                        
                        if ($result === false) {
                            showError('Chyba databáze', 'Nastala chyba čtení dat z databáze, zkuste to prosím později.');
                        }
                    
                        $data = mysqli_fetch_all($result, MYSQLI_ASSOC);


                        $dataCount = count($data); // should be 1

                        $login = $data[0]['login'];

                        $borrowed = $data[0]['borrowed'];
                        $borrowed = !empty($borrowed) ? explode(',', $borrowed) : [];

                        
                        $borrowed = array_diff($borrowed, [$bookId]);
                        $borrowed = implode(',', $borrowed);
                    }

                    */
                }


                if ($dataCount < 1) {
                    showError('Chyba', 'Nepodařilo se najít uživatele, který má tuto knihu půjčenou.', '/admin');
                }
                

                // update users
                $stmt = mysqli_prepare($conn, "UPDATE users SET borrowed = ? WHERE login=?");
                mysqli_stmt_bind_param($stmt, "ss", $borrowed,  $login);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);


                if ($dataCount > 1) { 
                    showError('Varování', 'V databázi uživatelů má tuto knihu půjčenou i jiný uživatel. Opakujte prosím tuto akci', '/admin', true);
                }
            }


            else {
                //TODO: vymazat u jednoho usera a druhemu to pridat nebo napsat chybu
                showError('Úprava se nezdařila', 'Kniha musí být před dalším půjčením vrácena do knihovny.', '/admin');
            }
        }



        //TODO: reservation
        if (($oldReservation != $reservation) || ($editAll && $editReservation)) {

            if (!$editAll) {
                $login = empty($oldReservation) ? $reservation : $oldReservation; // get right login
                mysqli_real_escape_string($conn, $login);
            }

            else {
                $login = $reservation;
            }
            

            $sql = "SELECT reserved FROM users WHERE login = '$login'";
            $result = mysqli_query($conn, $sql);
            
            if ($result === false) {
                echo 'Error: '.mysqli_error($conn);
            }
        
            $data = mysqli_fetch_all($result, MYSQLI_ASSOC);


            $bookId = $id;

            $reserved = $data[0]['reserved'];

            // get array
            !empty($reserved) ? $reserved = explode(',', $reserved): $reserved = [];


            // bookId is not in reserved - reserve book
            if ((empty($oldReservation) && !empty($reservation) && !in_array($bookId, $reserved) && !$editAll) || ($editAll && !empty($reservation))) { 
                if (!$editAll) {
                    // reserved
                    array_push($reserved, $bookId); // add new book id
                }


                else {
                    returnMultipleBooks($conn, $bookIdList, 'reserved'); // return book if reserved

                    foreach ($bookIdList as $bookId) {
                        //reserved
                        array_push($reserved, $bookId); // add new book id
                    }
                }


                // array to string
                $reserved = implode(',', $reserved); 
                

                // update users
                $stmt = mysqli_prepare($conn, "UPDATE users SET reserved = ? WHERE login=?");
                mysqli_stmt_bind_param($stmt, "ss", $reserved, $login);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
            }


            else if ((!empty($oldReservation) && empty($reservation) && !$editAll) || ($editAll && empty($reservation))) {// book is in users - return book
                if (!$editAll) {
                    $reserved = array_diff($reserved, array($bookId));

                    // array to string
                    $reserved = implode(',', $reserved);

                    $dataCount = 1;
                }


                else { // edit all 
                    returnMultipleBooks($conn, $bookIdList, 'reserved');
                }


                

                // update users
                $stmt = mysqli_prepare($conn, "UPDATE users SET reserved = ? WHERE login=?");
                mysqli_stmt_bind_param($stmt, "ss", $reserved,  $login);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
            }


            else {
                //TODO: vymazat u jednoho usera a druhemu to pridat nebo napsat chybu
                showError('Úprava se nezdařila', 'Rezervace musí být před dalším rezervováním zrušena.', '/admin');
            }
        }





        //UPDATE BOOKS - moved

        if (!$editAll) {
            $stmt = mysqli_prepare($conn, "UPDATE books SET registration = ?, isbn = ?, subject = ?, class = ?, publisher = ?, author = ?, name = ?, price = ?, dateAdded = ?, lentTo = ?, lendDate = ?, returnDate = ?, history = ?, reservation = ?, reservationExpiration = ?, note = ?, discarded = ? WHERE id=?");
            mysqli_stmt_bind_param($stmt, "ssssssssssssssssii", $registration, $isbn, $subject, $class, $publisher, $author, $name, $price, $dateAdded, $lentTo, $lendDate, $returnDate, $history, $reservation, $reservationExpiration, $note, $discarded, $id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }


        else {
            echo '<br> editAll';

            // update
            if (!mysqli_query($conn, $booksSql)) {
                showError('Chyba databáze', 'Nepodařilo se aktualizovat tabulku books.', '/admin');
            }


            //history
            if ($editLentTo && !empty($lentTo)) { 
                foreach ($bookIdList as $bookId) {
                    $query = "SELECT history FROM books WHERE id = $bookId";
                    $result = mysqli_query($conn, $query);
                    $row = mysqli_fetch_assoc($result);
                    $history = $row['history'];


                    if ($history != '') {
                        $history = $history . ', ' . $lentTo;
                    }

                    else {
                        $history = $lentTo;
                    }


                    // update
                    $sql = "UPDATE books SET history='$history' WHERE id=$bookId";
                    $result = mysqli_query($conn, $sql);

                    
                    if ($result === false) {
                        showError('Chyba databáze', 'Nastala nahrání historie do books databáze, zkuste to prosím později.');
                    }
                }
            }
            
        }


    

        $conn->close();
    
        // send form only once
        header('Location: index.php');
        exit;
    }

    //delete

    if (isset($_POST['delete'])) {
        $id = $_POST['id'];


        try {
            $conn = mysqli_connect('localhost', $sqlUser, $sqlPassword, $database);
        }
        
        catch (mysqli_sql_exception $e) {
            showError('Chyba připojení', "Nastala chyba připojení k databázi, zkuste to prosím později.");
        }


        if (!$conn) {
            showError('Chyba databáze', 'Nastala chyba čtení dat z databáze, zkuste to prosím později.');
        }


        $stmt = mysqli_prepare($conn, "DELETE FROM books WHERE id=?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        $conn->close();

        // send form only once
        header('Location: index.php');
        exit;
    }




    function returnMultipleBooks($conn, $bookIdList, $type) {
        $maxDataCount = 0;

        foreach ($bookIdList as $bookId) {
            if (is_string($bookId)) { // ecape if string
                $bookId = mysqli_real_escape_string($conn, $bookId);
            }


            $sql = "SELECT login, $type FROM users WHERE ($type LIKE '$bookId,%') OR ($type LIKE '%,$bookId') OR ($type LIKE '%,$bookId,%') OR ($type = '$bookId')";
            echo "<br> sql: $sql <br>";
            $result = mysqli_query($conn, $sql);

            
            
            if ($result === false) {
                showError('Chyba databáze', 'Nastala chyba čtení dat z databáze, zkuste to prosím později.');
            }
        
            $data = mysqli_fetch_all($result, MYSQLI_ASSOC);

            print_r($data);

            $dataCount = count($data); // should be 1

            $maxDataCount = $dataCount > $maxDataCount ? $dataCount : $maxDataCount;


            if ($dataCount != 0) {
                $login = $data[0]['login'];

                $reserved = $data[0][$type];
                $reserved = !empty($reserved) ? explode(',', $reserved) : [];

                
                $reserved = array_diff($reserved, [$bookId]);
                $reserved = !empty($reserved) ? implode(',', $reserved) : null;

                echo "return $bookId, login $login <br>";



                // update users
                $stmt = mysqli_prepare($conn, "UPDATE users SET $type = ? WHERE login=?");
                mysqli_stmt_bind_param($stmt, "ss", $reserved,  $login);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
            }
        }

        return $maxDataCount;
    }
?>


<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css">
    <title>Administrace</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Merriweather&family=Playfair+Display&family=Roboto&display=swap" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@48,400,0,0" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
</head>
<body>
    
    <?php
        $h1 = 'Administrace';
        include 'adminHeader.php'; 
    ?>


    <main class="container">
        <div class="adminButtons">
            <form class="addBtnForm" action="addBook.php" method="post">
                <input class='addBtn btn' type="submit" value="Přidat knihu">
            </form>

            <a href="downloadData.php" class="material-symbols-outlined downloadBtn" title="Stáhnout">
                <img class="downloadImg" src="/img/download.svg" alt="Stáhnout">
            </a>
        </div>
        

        <div class='filters'>
            <input type="text" class='searchInput input' class='search' id='searchInput' placeholder='Vyhledat'>
            <label class='showDiscardedLabel label' for="showDiscarded">Zobrazit vyřazené</label>
            <input class='showDiscarded checkbox' type="checkbox" name="showDiscarded" id="showDiscarded">
        </div>

        <?php
            try {
                $conn = mysqli_connect('localhost', $sqlUser, $sqlPassword, $database);
            }
            
            catch (mysqli_sql_exception $e) {
                showError('Chyba připojení', "Nastala chyba připojení k databázi, zkuste to prosím později.");
            }


            if (!$conn) {
                showError('Chyba databáze', 'Nastala chyba čtení dat z databáze, zkuste to prosím později.');
            }

            // get count
            $sql = "SELECT COUNT(*) FROM books WHERE discarded=0";
            $result = mysqli_query($conn, $sql);

            if ($result === false) {
                showError('Chyba databáze', 'Nastala chyba čtení dat z databáze, zkuste to prosím později.');
            }

            $resultCount = mysqli_fetch_all($result, MYSQLI_ASSOC)[0]['COUNT(*)'];


            // not returned
            $sql = "SELECT COUNT(*) AS count FROM books WHERE returnDate < CURDATE()";
            $result = mysqli_query($conn, $sql);

            if ($result === false) {
                showError('Chyba databáze', 'Nastala chyba čtení dat z databáze, zkuste to prosím později.');
            }

            else {
                $row = mysqli_fetch_assoc($result);
                $notReturnedCount = $row['count'];
            }

            echo "
            <div class='info'>
            <div class='info-block'>
                <h2>Počet výsledků:</h2>
                <p class='result-count' id='result-count'>$resultCount</p>
            </div>
            <div class='info-block' id='notReturned'>
                <h2>Nevrácené knihy:</h2>
                <p class='unreturned-books'>$notReturnedCount</p>
            </div>
            </div>
            ";

        ?>  
        

        

        <table class='dataTable'>
            <thead>
                <tr class='headerRow'>
                    <th class='firstTd'>
                        <form action='editBook.php' method='post'>
                            <input type='hidden' name='id' value='all'>
                            <button class='editBtn' type='submit'>Upravit vše</button>
                        </form>
                    </th>
                    <th id='registration'>Evidenční číslo</th>
                    <th id='subject'>Okruh</th>
                    <th id='author'>Autor</th>
                    <th id='name'>Název</th>
                    <th id='price'>Cena</th>
                    <th id='dateAdded'>Zapsáno</th>
                    <th id='lentTo'>Půjčeno</th>
                    <th id='class'>Třída</th>
                    <th id='lendDate'>Datum půjčení</th>
                    <th id='reservation'>Rezervace</th>
                    <th id='note'>Poznámka</th>
                </tr>
            </thead>

            <tbody id='tableBody'>
                <?php
                    $rows = 300;

                    try {
                        $conn = mysqli_connect('localhost', $sqlUser, $sqlPassword, $database);
                    }
                    
                    catch (mysqli_sql_exception $e) {
                        showError('Chyba připojení', "Nastala chyba připojení k databázi, zkuste to prosím později.");
                    }


                    $sql = "SELECT * FROM books WHERE discarded=0 ORDER BY `books`.`id` DESC LIMIT $rows";
                    $result = mysqli_query($conn, $sql);

                    if ($result === false) {
                        echo 'Error: '.mysqli_error($conn);
                    }

                    $data = mysqli_fetch_all($result, MYSQLI_ASSOC);


                    if (count($data) < $rows) {
                        $rows = count($data);
                    }


                    $notRetunedCount = 0;

                    for ($i = 0; $i < $rows; $i++) {
                        
                        $id = $data[$i]['id'];
                        $registration = $data[$i]['registration'];
                        $isbn = $data[$i]['isbn'];
                        $subject = $data[$i]['subject'];
                        $publisher = $data[$i]['publisher'];
                        $author = $data[$i]['author'];
                        $name = $data[$i]['name'];
                        $price = $data[$i]['price'];
                        $dateAdded = $data[$i]['dateAdded'];
                        $lentTo = $data[$i]['lentTo'];
                        $class = $data[$i]['class'];
                        $lendDate = $data[$i]['lendDate'];
                        $returnDate = $data[$i]['returnDate'];
                        $reservation = $data[$i]['reservation'];
                        $note = $data[$i]['note'];
                        $discarded = $data[$i]['discarded'];

                        if ($i % 2 === 0) {
                            $evenNum = true;
                        }

                        else {
                            $evenNum = false;
                        }


                        // book wasnt returned in time
                        
                        $returnedInTime = true;
                        $returnedInTimeClass = '';

                        if ($returnDate != null) {
                            $returnDateObj = new DateTime($returnDate);
                            $todayDateObj = new DateTime();


                            if ($returnDateObj < $todayDateObj &&  $returnDate != '') {
                                $returnedInTime = false;
                                $returnedInTimeClass = 'notReturned';
                            }
                        }

                       
                        



                        echo "<tr class='dataRow " . ($i % 2 === 1 ? 'evenRow' : '') . ' ' . ($discarded == 1 ? 'discardedRow' : '') . ' ' . $returnedInTimeClass . " '>
                            <td class='firstTd'>
                                <form action='editBook.php' method='post'>
                                    <input type='hidden' name='id' value='$id'>
                                    <button class='editBtn' type='submit'>Upravit</button>
                                </form>
                            </td>
                            <td>$registration</td>
                            <td>$subject</td>
                            <td>$author</td>
                            <td>$name</td>
                            <td>$price</td>
                            <td>$dateAdded</td>
                            <td>$lentTo</td>
                            <td>$class</td>
                            <td>$lendDate</td>
                            <td>$reservation</td>
                            <td>$note</td>
                        </tr>";
                    }


                    echo "

                    "
                ?>
            </tbody>
        </table>

        <button class='expandTableBtn btn' onclick="loadMoreRows(300)">Další</button>

    </main>


    <!-- expandTableAJAX -->
    <script>
        var rowsShown = <?php echo $rows; ?>;
        
        function loadMoreRows(addRows) {
            let searchInput = document.getElementById('searchInput');
            let showDiscarded = document.getElementById('showDiscarded');

            columns = [registration, subject, author, name, price, dateAdded, lentTo, $class, lendDate, reservation, note];
            
            console.log(searchInput.value);
            console.log(showDiscarded.checked);
            console.log(columns);
            

            $.ajax({
                url: '/getDataForAjax/expandTable.php',
                type: 'GET',
                data: {
                    count: addRows,
                    rowCount: rowsShown,
                    searchInput: searchInput.value,
                    showDiscarded: showDiscarded.checked,
                    columns: columns
                },
                success: function(response) {
                    var tbody = $('#tableBody');
                    tbody.append(response);
                    rowsShown += addRows;
                }
            });
        }


        // search

        let searchInput = document.getElementById('searchInput');
        let showDiscarded = document.getElementById('showDiscarded');
        searchInput.addEventListener('input', search);
        showDiscarded.addEventListener('input', search);

        // search by
        let registration = false;
        let subject = false;
        let author = false;
        let name = false;
        let price = false;
        let dateAdded = false;
        let lentTo = false;
        let $class = false;
        let lendDate = false;
        let reservation = false;
        let note = false;

        var columns = [registration, subject, author, name, price, dateAdded, lentTo, $class, lendDate, reservation, note];

        var registrationEl = document.getElementById('registration');
        let subjectEl = document.getElementById('subject');
        let authorEl = document.getElementById('author');
        let nameEl = document.getElementById('name');
        let priceEl = document.getElementById('price');
        let dateAddedEl = document.getElementById('dateAdded');
        let lentToEl = document.getElementById('lentTo');
        let $classEl = document.getElementById('class');
        let lendDateEl = document.getElementById('lendDate');
        let reservationEl = document.getElementById('reservation');
        let noteEl = document.getElementById('note');


        registrationEl.addEventListener('click', function() {
            registration = !registration; // toggle searching by the column
            (searchInput.value != '') ? search() : ''; // change searched column

            //style
            let color = '#2693ff';
            registration ? color = '#2667ff' : color = '#2693ff';
            registrationEl.style.backgroundColor = color;
        });
        document.getElementById('subject').addEventListener('click', function() {
            subject = !subject;
            (searchInput.value != '') ? search() : ''; // change searched column

            //style
            let color = '#2693ff';
            subject ? color = '#2667ff' : '#2693ff';
            subjectEl.style.backgroundColor = color;
        });
        document.getElementById('author').addEventListener('click', function() {
            author = !author;
            (searchInput.value != '') ? search() : ''; // change searched column

            //style
            let color = '#2693ff';
            author ? color = '#2667ff' : '#2693ff';
            authorEl.style.backgroundColor = color;
        });
        document.getElementById('name').addEventListener('click', function() {
            name = !name;
            (searchInput.value != '') ? search() : ''; // change searched column

            //style
            let color = '#2693ff';
            name ? color = '#2667ff' : '#2693ff';
            nameEl.style.backgroundColor = color;
        });
        document.getElementById('price').addEventListener('click', function() {
            price = !price;
            (searchInput.value != '') ? search() : ''; // change searched column

            //style
            let color = '#2693ff';
            price ? color = '#2667ff' : '#2693ff';
            priceEl.style.backgroundColor = color;
        });
        document.getElementById('dateAdded').addEventListener('click', function() {
            dateAdded = !dateAdded;
            (searchInput.value != '') ? search() : ''; // change searched column

            //style
            let color = '#2693ff';
            dateAdded ? color = '#2667ff' : '#2693ff';
            dateAddedEl.style.backgroundColor = color;
        });
        document.getElementById('lentTo').addEventListener('click', function() {
            lentTo = !lentTo;
            (searchInput.value != '') ? search() : ''; // change searched column

            //style
            let color = '#2693ff';
            lentTo ? color = '#2667ff' : '#2693ff';
            lentToEl.style.backgroundColor = color;
        });
        document.getElementById('class').addEventListener('click', function() {
            $class = !$class;
            (searchInput.value != '') ? search() : ''; // change searched column

            //style
            let color = '#2693ff';
            $class ? color = '#2667ff' : '#2693ff';
            $classEl.style.backgroundColor = color;
        });
        document.getElementById('lendDate').addEventListener('click', function() {
            lendDate = !lendDate;
            (searchInput.value != '') ? search() : ''; // change searched column

            //style
            let color = '#2693ff';
            lendDate ? color = '#2667ff' : '#2693ff';
            lendDateEl.style.backgroundColor = color;
        });
        document.getElementById('reservation').addEventListener('click', function() {
            reservation = !reservation;
            (searchInput.value != '') ? search() : ''; // change searched column

            //style
            let color = '#2693ff';
            reservation ? color = '#2667ff' : '#2693ff';
            reservationEl.style.backgroundColor = color;
        });
        document.getElementById('note').addEventListener('click', function() {
            note = !note;
            (searchInput.value != '') ? search() : ''; // change searched column

            //style
            let color = '#2693ff';
            note ? color = '#2667ff' : '#2693ff';
            noteEl.style.backgroundColor = color;
        });


        // not returned
        let showNotReturned = false;

        document.getElementById('notReturned').addEventListener('click', function() {
            showNotReturned = !showNotReturned;
            console.log(showNotReturned);
            search();
        })




        function search() {
            console.log('search')

            searchInput = document.getElementById('searchInput');
            showDiscarded = document.getElementById('showDiscarded');

            
            // delete search by cookies if search bar is empty
            if (searchInput.value == '') {
                document.cookie = `searchBy=;`;
                console.log(getCookie('searchBy'));
            }

            else {
                console.log(getCookie('searchBy'))
            }

            columns = [registration, subject, author, name, price, dateAdded, lentTo, $class, lendDate, reservation, note];


            $.ajax({
                url: '/getDataForAjax/getSearchedData.php',
                type: 'GET',
                data: {
                    searchInput: searchInput.value,
                    showDiscarded: showDiscarded.checked,
                    columns: columns,
                    showNotReturned: showNotReturned
                },
                success: function(response) {
                    let tbody = $('#tableBody');
                    let resultCount = $('#result-count');

                    response = response.split('###datasplit###');

                    resultCount.html(response[0])
                    tbody.html(response[1]);
                }
            });
        }

        // set cookies for downloading data
        searchInput.addEventListener('input', setDownloadCookies);
        showDiscarded.addEventListener('input', setDownloadCookies);

        document.cookie = `searchInput=''`;
        document.cookie = `showDiscarded=false`;


        function setDownloadCookies() {
            searchInput = document.getElementById('searchInput');
            showDiscarded = document.getElementById('showDiscarded');

            document.cookie = `searchInput=${searchInput}`;
            document.cookie = `showDiscarded=${showDiscarded}`;
        }


        // Funkce pro získání hodnoty cookie podle názvu
        function getCookie(name) {
            var cookieName = name + "=";
            var decodedCookie = decodeURIComponent(document.cookie);
            var cookieArray = decodedCookie.split(";");

            for (var i = 0; i < cookieArray.length; i++) {
                var cookie = cookieArray[i].trim();
                if (cookie.indexOf(cookieName) === 0) {
                    return cookie.substring(cookieName.length, cookie.length);
                }
            }

            return "";
        }
    </script>


    <footer>

    </footer>
</body>
</html>