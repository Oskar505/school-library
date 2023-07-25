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
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Knihovna gykovy</title>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="/styles.css">
</head>
<body>
    <header class="indexHeader container">
        <div class="headerContainer">
            <img class="logo" src="https://www.gykovy.cz/wp-content/uploads/2021/02/cropped-cropped-GYKOVY-LOGO_bila-budova-okoli-kruhu_web-1.png" alt="gykovy logo">
            <h1 class="mainH1">Školní knihovna GYKOVY</h1>
            
            <?php
                session_start();

                if (isset($_SESSION['userLoggedIn']) || isset($_SESSION['loggedin'])) {
                    echo "<a class='loginLink' href='admin/logout.php'>" . $_SESSION['firstName'] /*. ' ' . $_SESSION['lastName']*/ . "<br> Odhlásit se</a>";
                }
                

                else {
                    echo "<a class='loginLink' href='userLogin.php'>Přihlásit se</a>";
                }
            ?>
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

            

            <input class="searchInput" id="searchInput" type="text" placeholder="Vyhledat" value="">


            <table class="dataTable">
                <thead>
                    <tr>
                        <th>Autor</th>
                        <th>Název</th>
                        <th>Stav</th>
                    </tr>
                </thead>

                <tbody id="tableBody">
                    <?php
                        $conn = mysqli_connect('localhost', $sqlUser, $sqlPassword, $database);

                        if (!$conn) {
                            echo 'chyba pripojeni'.mysqli_connect_error();
                        }
    
    
                        $sql = "SELECT id, author, name, returnDate, reservation FROM books WHERE discarded=0 LIMIT 50";
                        $result = mysqli_query($conn, $sql);
    
                        if ($result === false) {
                                echo 'Error: '.mysqli_error($conn);
                        }
    
    
                        $data = mysqli_fetch_all($result, MYSQLI_ASSOC);
    
    

                        if (count($data) < 50) {
                            $rows = count($data);
                        }


                        for ($i = 0; $i < 50; $i++) {
                            $id = $data[$i]['id'];
                            $author = $data[$i]['author'];
                            $name = $data[$i]['name'];
                            $returnDate = $data[$i]['returnDate'];
                            $reservation = $data[$i]['reservation'];

                            $state = 'V knihovně';

                            if ($returnDate != '') {
                                if ($reservation != '') {
                                    $availableDate = date('Y-m-d', strtotime($returnDate . ' +7 days'));

                                    $state = "Zarezervováno do $availableDate";
                                }

                                else {
                                    $state = "Půjčeno do $returnDate";
                                } 
                            }

                            else {
                                if ($reservation != '') {
                                    $state = "Zarezervováno do +3 dní";
                                }
                            }


                            

                            


                            echo "
                                <tr onclick=\"openMoreInfo('$id', '$name')\">
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
    

    <script>
        function openMoreInfo(id, name) {
            window.location.href = 'moreInfo.php?id=' + id;
        }



        // search

        let searchInput = document.getElementById('searchInput');
        searchInput.addEventListener('input', search);



        function search() {
            console.log('search')

            searchInput = document.getElementById('searchInput');

            console.log(searchInput.value);

            $.ajax({
                url: 'getSearchedDataUser.php',
                type: 'GET',
                data: {
                    searchInput: searchInput.value,
                },
                success: function(response) {
                    var tbody = $('#tableBody');
                    tbody.html(response);
                }
            });
        }
    </script>
</body>
</html>