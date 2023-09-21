<?php
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    require('/var/secrets.php');

    $sqlUser = $secrets['sql-user'];
    $sqlPassword = $secrets['sql-password'];
    $database = $secrets['sql-database'];



    try {
        $conn = mysqli_connect('localhost', $sqlUser, $sqlPassword, $database);
    }
    
    catch (mysqli_sql_exception $e) {
        showError('Chyba připojení', "Nastala chyba připojení k databázi, zkuste to prosím později.");
    }

    if (!$conn) {
        showError('Chyba databáze', 'Nastala chyba čtení dat z databáze, zkuste to prosím později.');
    }



    $bookIdList = [2, 5];

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

        $borrowed = $data[0]['borrowed'];
        $borrowed = !empty($borrowed) ? explode(',', $borrowed) : [];

        
        $borrowed = array_diff($borrowed, [$bookId]);
        $borrowed = implode(',', $borrowed);

        print_r($data);
        echo '<br>';
        echo "borrowed: $borrowed";
        echo '<br>';
        echo $dataCount;
        echo '<br>';
    }
?>