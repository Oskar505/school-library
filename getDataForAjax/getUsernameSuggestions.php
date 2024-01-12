<?php
    session_start();

    if (!isset($_SESSION['loggedin'])) {
        header('Location: /userLogin.php');
        exit;
    }

    

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
    $sql = "SELECT login, class, firstName, lastName FROM users WHERE login LIKE '%" . $query . "%' LIMIT 10";

    $result = mysqli_query($conn, $sql);
    
    if ($result === false) {
        echo 'Error: '.mysqli_error($conn);
    }

    $data = mysqli_fetch_all($result, MYSQLI_ASSOC);

    if (count($data) > 0) {
        echo json_encode($data);
    }
    
    else {
        echo json_encode(array('Tento uživatel neexistuje'));
    }

    $conn->close();
?>