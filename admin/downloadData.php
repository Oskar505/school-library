<?php
    session_start();

    if (!isset($_SESSION['loggedin'])) {
        header('Location: login.php');
        exit;
    }


    require('/var/secrets.php');
    $sqlUser = $secrets['sql-user'];
    $sqlPassword = $secrets['sql-password'];
    $database = $secrets['sql-database'];

    $conn = mysqli_connect('localhost', $sqlUser, $sqlPassword, $database);

    if (!$conn) {
        echo 'Připojení k databázi se nezdařilo';
    }


    // get cookies, if cookie doesn't exist set it to ''
    $searchBy = isset($_COOKIE['searchBy']) ? $_COOKIE['searchBy'] : '';
    $showDiscarded = isset($_COOKIE['showDiscarded']) ? $_COOKIE['showDiscarded'] : 'false';
    $searchInput = isset($_COOKIE['searchInput']) ? $_COOKIE['searchInput'] : '';



    if ($showDiscarded == 'true') {
        if ($searchBy == '') {
            $sql = "SELECT * FROM books";
        }

        else {
            $sql = "SELECT * FROM books WHERE $searchBy";
        }
    }

    else {
        if ($searchBy == '') {
            $sql = "SELECT * FROM books WHERE discarded=0";
        }

        else {
            $sql = "SELECT * FROM books WHERE ($searchBy) AND discarded=0";
        }     
    }


    $result = mysqli_query($conn, $sql);

    if ($result === false) {
            echo 'Error: '.mysqli_error($conn);
    }

    $data = mysqli_fetch_all($result, MYSQLI_ASSOC);

    //print_r($data);

    if ($result->num_rows > 0) {
        // Otevření souboru pro zápis
        $file = fopen("/var/www/html/admin/data/knihy.csv", "w");
        
        // Zápis hlavičky CSV souboru
        $header = array("Id", "Registrační č.", "Isbn", "Okruh", "Vydavatel", "Autor", "Název", "Cena", "Datum zapsání", "Půjčeno", "Třída", "Datum půjčení", "Datum vrácení", "Rezervace", "Poznámka", "Vyřazeno");
        fputcsv($file, $header);

        // Zápis dat získaných z SQL dotazu do CSV souboru
        foreach ($data as $row) {
            $row['discarded'] = ($row['discarded'] == '0') ? 'ne':'ano';
            $rowData = array($row['id'], $row['registration'], $row['isbn'], $row['subject'], $row['publisher'], $row['author'], $row['name'], $row['price'], $row['dateAdded'], $row['lentTo'], $row['class'], $row['lendDate'], $row['returnDate'], $row['reservation'], $row['note'], $row['discarded']);
            fputcsv($file, $rowData);
        }

        // Uzavření souboru
        fclose($file);
    }

    // Uzavření spojení s databází
    $conn->close();

    // Stahování CSV souboru
    $file = '/var/www/html/admin/data/knihy.csv';
    
    // Nastavení HTTP hlaviček
    header('Content-Description: File Transfer');
    header('Content-Type: application/csv');
    header('Content-Disposition: attachment; filename="' . basename($file) . '"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($file));
    
    // Odeslání souboru do výstupního bufferu
    readfile($file);
    
    // Smazání CSV souboru po odeslání
    unlink($file);


    exit;
?>