<?php
    // get secrets
    require('/var/secrets.php');

    $sqlUser = $secrets['sql-user'];
    $sqlPassword = $secrets['sql-password'];
    $database = $secrets['sql-database'];

    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Knihovna gykovy</title>

    <link rel="stylesheet" href="/styles.css">
</head>
<body>
    <header class="indexHeader container">
        <div class="headerContainer">
            <img class="logo" src="https://www.gykovy.cz/wp-content/uploads/2021/02/cropped-cropped-GYKOVY-LOGO_bila-budova-okoli-kruhu_web-1.png" alt="gykovy logo">
            <h1>Školní knihovna GYKOVY</h1>
            <a class='adminLink' href="/admin/index.php">Administrace</a>
        </div>

        <nav>
            <a href="">lorem</a>
            <a href="">ipsum</a>
            <a href="">dolor</a>
            <a href="">sit</a>
            <a href="">amet</a>
        </nav>
    </header>



    <main class="container">
        <div class="dataContainer">
            <input class="searchInput" type="text" placeholder="Vyhledat">


            <table class="dataTable">
                <thead>
                    <tr>
                        <th>Autor</th>
                        <th>Název</th>
                        <th>Stav</th>
                    </tr>
                </thead>

                <tbody>
                    <?php
                        $conn = mysqli_connect('localhost', $sqlUser, $sqlPassword, $database);

                        if (!$conn) {
                            echo 'chyba pripojeni'.mysqli_connect_error();
                        }
    
    
                        $sql = "SELECT author, name, returnDate, reservation FROM books WHERE discarded=0 LIMIT 50";
                        $result = mysqli_query($conn, $sql);
    
                        if ($result === false) {
                                echo 'Error: '.mysqli_error($conn);
                        }
    
    
                        $data = mysqli_fetch_all($result, MYSQLI_ASSOC);
    
    

                        if (count($data) < 50) {
                            $rows = count($data);
                        }


                        for ($i = 0; $i < 50; $i++) {
                            $author = $data[$i]['author'];
                            $name = $data[$i]['name'];
                            $returnDate = $data[$i]['returnDate'];
                            $reservation = $data[$i]['reservation'];



                            $state = 'stav';


                            echo "
                                <tr>
                                    <td>$author</td>
                                    <td>$name</td>
                                    <td>$state</td>
                                </tr>
                            ";
                        }
                    ?>
                </tbody>
            </table>
        </div>
    </main>



    <footer>

    </footer>
</body>
</html>