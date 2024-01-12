<?php
    session_start();

    if (!isset($_SESSION['loggedin'])) {
        header('Location: /userLogin.php');
        exit;
    }

    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);



    require('/var/secrets.php');
    $sqlUser = $secrets['sql-user'];
    $sqlPassword = $secrets['sql-password'];
    $database = $secrets['sql-database'];

    $conn = mysqli_connect('localhost', $sqlUser, $sqlPassword, $database);

    if (!$conn) {
        echo 'Připojení k databázi se nezdařilo';
    }


    if (isset($_POST['upload_old'])) {
        $fileMimes = array(
            'text/x-comma-separated-values',
            'text/comma-separated-values',
            'application/octet-stream',
            'application/vnd.ms-excel',
            'application/x-csv',
            'text/x-csv',
            'text/csv',
            'application/csv',
            'application/excel',
            'application/vnd.msexcel',
            'text/plain'
        );


     
        // Validate selected file is a CSV file or not
        if (!empty($_FILES['file']['name']) && in_array($_FILES['file']['type'], $fileMimes)) {
     
            // Open uploaded CSV file with read-only mode
            $csvFile = fopen($_FILES['file']['tmp_name'], 'r');
    
            // Skip the first line
            //fgetcsv($csvFile);
    
            // Parse data from CSV file line by line        
            while (($getData = fgetcsv($csvFile, 10000, ",")) !== FALSE){

                // lendDate
                if ($getData[11] == '0000-00-00') {
                    $getData[11] = '';
                }

                // FIXME:
                /*
                // dates
                $getData[7] = date("d-m-Y", strtotime($getData[7]));
                $getData[11] = date("d-m-Y", strtotime($getData[11]));

                // Přidání 3 měsícu k datu pujceni
                $returnDate = date("Y-d-m", strtotime("+3 months", strtotime($getData[11])));
                */

                // discarded
                if (strpos($getData[14], 'VYŘAZENO') !== false) {
                    $discarded = 1;
                }

                else {
                    $discarded = 0;
                }



                $allData = [$getData[1], '', $getData[2], $getData[10], '', $getData[4], $getData[5], $getData[6], $getData[7], $getData[9], $getData[11], $returnDate, '', '', $getData[14], $discarded];
    
                $stmt = mysqli_prepare($conn, "INSERT INTO books (registration, isbn, subject, class, publisher, author, name, price, dateAdded, lentTo, lendDate, returnDate, reservation, reservationExpiration, note, discarded) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

                mysqli_stmt_bind_param($stmt, "sssssssssssssssi", $allData[0], $allData[1], $allData[2], $allData[3], $allData[4], $allData[5], $allData[6], $allData[7], $allData[8], $allData[9], $allData[10], $allData[11], $allData[12], $allData[13], $allData[14], $allData[15]);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
            }
    
            // Close opened CSV file
            fclose($csvFile);
    


            // send form only once
            header('Location: index.php');
            exit;  
        }

        else{
            echo "Please select valid file";
        }
    }




    if (isset($_POST['upload_old_mod'])) {
        $fileMimes = array(
            'text/x-comma-separated-values',
            'text/comma-separated-values',
            'application/octet-stream',
            'application/vnd.ms-excel',
            'application/x-csv',
            'text/x-csv',
            'text/csv',
            'application/csv',
            'application/excel',
            'application/vnd.msexcel',
            'text/plain'
        );


     
        // Validate selected file is a CSV file or not
        if (!empty($_FILES['file']['name']) && in_array($_FILES['file']['type'], $fileMimes)) {

            echo 'test';
     
            // Open uploaded CSV file with read-only mode
            $csvFile = fopen($_FILES['file']['tmp_name'], 'r');
    
            // Skip the first line
            fgetcsv($csvFile);


            // delete lend data in users
            /*
            $sql = "UPDATE users SET borrowed = null";
            $result = mysqli_query($conn, $sql);
            
            if ($result === false) {
                showError('Chyba databáze', 'Nastala chyba čtení dat z databáze, zkuste to prosím později.');
            }
            */
    
            // Parse data from CSV file line by line        
            while (($getData = fgetcsv($csvFile, 10000, ",")) !== FALSE){

                foreach ($getData as &$col) {
                    if ($col == '0000-00-00') {
                        $col = null;
                    }
                }

                // lendDate
                

                // FIXME:
                /*
                // dates
                $getData[7] = date("d-m-Y", strtotime($getData[7]));
                $getData[11] = date("d-m-Y", strtotime($getData[11]));

                // Přidání 3 měsícu k datu pujceni
                $returnDate = date("Y-d-m", strtotime("+3 months", strtotime($getData[11])));
                */

                // discarded
                if (strpos($getData[17], 'VYŘAZENO') !== false) {
                    $discarded = 1;

                    // delete lend data
                    $getData[10] = null;
                    $getData[14] = null;
                    $getData[12] = null;
                }

                else {
                    $discarded = 0;
                }


                // note + reservation (both used as note)
                if (!empty($getData[11]) && !empty($getData[17])) {
                    $note = $getData[11] . ', ' . $getData[17];
                }

                else {
                    $note = $getData[11] . $getData[17];
                }


                // history
                $history = null;

                if (!empty($getData[10])) {
                    $history = $getData[10];
                }


                // delete lend data if there is no login
                else {
                    $getData[10] = null;
                    $getData[14] = null;
                    $getData[12] = null;
                }

                

                




                // BOOKS TABLE

                $allData = [$getData[1], null, $getData[2], $getData[13], null, $getData[4], $getData[5], $getData[6], $getData[7], $getData[10], $getData[14], $getData[12], $history, null, null, $note, $discarded];
    
                $stmt = mysqli_prepare($conn, "INSERT INTO books (registration, isbn, subject, class, publisher, author, name, price, dateAdded, lentTo, lendDate, returnDate, history, reservation, reservationExpiration, note, discarded) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

                mysqli_stmt_bind_param($stmt, "ssssssssssssssssi", $allData[0], $allData[1], $allData[2], $allData[3], $allData[4], $allData[5], $allData[6], $allData[7], $allData[8], $allData[9], $allData[10], $allData[11], $allData[12], $allData[13], $allData[14], $allData[15], $allData[16]);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);




                // LINK USER

                if (!empty($getData[10])) {
                    $login = mysqli_real_escape_string($conn, $getData[10]);

                    // get last book id
                    $sql = "SELECT id FROM books ORDER BY id DESC LIMIT 1";
                    $result = mysqli_query($conn, $sql);
                    
                    if ($result === false) {
                        showError('Chyba databáze', 'Nastala chyba čtení dat z databáze, zkuste to prosím později.');
                    }
                
                    $data = mysqli_fetch_all($result, MYSQLI_ASSOC);
                    $bookId = $data[0]['id'];


                    // get previous borrowed data
                    $sql = "SELECT borrowed, borrowedHistory FROM users WHERE login = '$login'";
                    $result = mysqli_query($conn, $sql);
                    
                    if ($result === false) {
                        showError('Chyba databáze', 'Nastala chyba čtení dat z databáze, zkuste to prosím později.');
                    }
                
                    $data = mysqli_fetch_all($result, MYSQLI_ASSOC);
                    $borrowed = $data[0]['borrowed'];
                    $borrowedHistory = $data[0]['borrowedHistory'];


                    if (strpos($borrowed, $bookId) !== false) {
                        empty($borrowed) ? $newBorrowed = $bookId : $newBorrowed = $borrowed . ',' . $bookId;
                        empty($borrowedHistory) ? $newBorrowedHistory = $bookId : $newBorrowedHistory = $borrowedHistory . ',' . $bookId;
                    }
                    


                    // update
                    $stmt = mysqli_prepare($conn, "UPDATE users SET borrowed = ?, borrowedHistory = ? WHERE login = ?");

                    mysqli_stmt_bind_param($stmt, "sss", $newBorrowed, $newBorrowedHistory, $login);
                    mysqli_stmt_execute($stmt);
                    mysqli_stmt_close($stmt);
                }
            }
    
            // Close opened CSV file
            fclose($csvFile);
    


            // send form only once
            //header('Location: index.php');
            exit;  
        }

        else{
            echo "Please select valid file";
        }
    }



    class uploadCsvData {
        public $fileMimes = array(
            'text/x-comma-separated-values',
            'text/comma-separated-values',
            'application/octet-stream',
            'application/vnd.ms-excel',
            'application/x-csv',
            'text/x-csv',
            'text/csv',
            'application/csv',
            'application/excel',
            'application/vnd.msexcel',
            'text/plain'
        );

        function __construct() {

        }
    }

?>




<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nahrát csv</title>
</head>
<body>
    <h1>Nahrát csv do databáze</h1>

    <div class="container">
        <form action="upload.php" method="post" enctype="multipart/form-data">
            <h2>Nová databáze</h2>
            <p>Csv soubor v novém formátu</p>
            <div class="input-group">
                <div class="custom-file">
                    <input type="file" class="custom-file-input" id="customFileInput" name="file">
                </div>
                <br>
                <div class="input-group-append">
                    <input type="submit" name="upload_new" value="Nahrát" class="btn btn-primary">
                </div>
            </div>
        </form>

        <form action="upload.php" method="post" enctype="multipart/form-data">
            <h2>Stará databáze</h2>
            <p>Csv soubor ze staré databáze</p>
            <div class="input-group">
                <div class="custom-file">
                    <input type="file" class="custom-file-input" id="customFileInput" name="file">
                </div>
                <br>
                <div class="input-group-append">
                    <input type="submit" name="upload_old" value="Nahrát" class="btn btn-primary">
                </div>
            </div>
        </form>

        <form action="upload.php" method="post" enctype="multipart/form-data">
            <h2>Stará databáze upravená</h2>
            <p>Upravený csv soubor ze staré databáze <br>
            csv soubor z prosince 2023 jsem uložil na disk knihovna@gykovy.cz
            </p>
            <div class="input-group">
                <div class="custom-file">
                    <input type="file" class="custom-file-input" id="customFileInput" name="file">
                </div>
                <br>
                <div class="input-group-append">
                    <input type="submit" name="upload_old_mod" value="Nahrát" class="btn btn-primary">
                </div>
            </div>
        </form>
    </div>
</body>
</html>