<?php
    // Připojení k databázi (nahraďte 'your_username', 'your_password' a 'your_database_name' vhodnými údaji)
    $host = 'localhost';
    
    // get secrets
    require('/var/secrets.php');

    $sqlUser = $secrets['sql-user'];
    $sqlPassword = $secrets['sql-password'];
    $database = $secrets['sql-database'];



    $conn = new mysqli($host, $sqlUser, $sqlPassword, $database);

    if ($conn->connect_error) {
        die("Chyba při připojení k databázi: " . $conn->connect_error);
    }

    // Získání hledaného textu z GET parametru
    $query = $_GET['query'];

    // Sestavení SQL dotazu na získání odpovídajících položek z databáze
    $sql = "SELECT login FROM users WHERE login LIKE '%" . $query . "%' LIMIT 10";

    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        // Vrácení výsledku jako JSON
        $suggestions = array();

        while ($row = $result->fetch_assoc()) {
            $suggestions[] = $row['login'];
        }

        echo json_encode($suggestions);
    }
    
    else {
        echo json_encode(array('Tento uživatel neexistuje'));
    }

    $conn->close();
?>