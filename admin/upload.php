<?php
    session_start();

    if (!isset($_SESSION['loggedin'])) {
        header('Location: login.php');
        exit;
    }



    $conn = mysqli_connect('localhost', 'test', 'Test22knih*', 'knihovna');

    if (!$conn) {
        echo 'Připojení k databázi se nezdařilo';
    }



    if (isset($_POST['upload'])) {
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
            fgetcsv($csvFile);
    
            // Parse data from CSV file line by line        
            while (($getData = fgetcsv($csvFile, 10000, ",")) !== FALSE){
                // Get row data
                $name = $getData[0];
                $email = $getData[1]   
                
                $allData = [$getData[0], '', $getData[2], $getData[9], '', $getData[4], $getData[5], $getData[6], $getData[7], $getData[8], $getData[10], $returnDate, $reservation, $note, $discarded];
    
                // If user already exists in the database with the same email
                $query = "SELECT id FROM users WHERE email = '" . $getData[1] . "'";
    
                $check = mysqli_query($con, $query);
    
                if ($check->num_rows > 0){
                    mysqli_query($conn, "UPDATE users SET name = '" . $name . "', created_at = NOW() WHERE email = '" . $email . "'");
                }
                
                else{
                    mysqli_query($con, "INSERT INTO users (name, email, created_at, updated_at) VALUES ('" . $name . "', '" . $email . "', NOW(), NOW())");
                }
            }
    
            // Close opened CSV file
            fclose($csvFile);
    
            header("Location: index.php");         
        }

        else{
            echo "Please select valid file";
        }
    }
        $stmt = mysqli_prepare($conn, "INSERT INTO books (registration, isbn, subject, class, publisher, author, name, price, dateAdded, lentTo, lendDate, returnDate, reservation, note, discarded) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        mysqli_stmt_bind_param($stmt, "ssssssssssssssi", $allData[0], $allData[1], $allData[2], $allData[3], $allData[4], $allData[5], $allData[6], $allData[7], $allData[8], $allData[9], $allData[10], $allData[11], $allData[12], $allData[13], $allData[14]);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        // send form only once
        header('Location: index.php');
        exit;

?>




<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nahrát csv</title>
</head>
<body>
    <h1>Nahrát csv do databáze</h1>
    <br>

    <div class="container">
        <form action="upload.php" method="post" enctype="multipart/form-data">
            <div class="input-group">
                <div class="custom-file">
                    <input type="file" class="custom-file-input" id="customFileInput" name="file">
                </div>
                <br>
                <div class="input-group-append">
                    <input type="submit" name="upload" value="Nahrát" class="btn btn-primary">
                </div>
            </div>
        </form>
    </div>
</body>
</html>