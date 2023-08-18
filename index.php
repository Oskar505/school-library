<?php
    // get secrets
    require('/var/secrets.php');

    $sqlUser = $secrets['sql-user'];
    $sqlPassword = $secrets['sql-password'];
    $database = $secrets['sql-database'];


    include 'functions.php';


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

    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@48,400,0,0" />
</head>
<body>
    <header class="indexHeader container">
        <div class="headerContainer">
            <img class="logo" src="https://www.gykovy.cz/wp-content/uploads/2021/02/cropped-cropped-GYKOVY-LOGO_bila-budova-okoli-kruhu_web-1.png" alt="gykovy logo">
            <h1 class="mainH1">Školní knihovna GYKOVY</h1>
            
            <div class="userBar">
                <div class="material-symbols-outlined myBooksIcon">book</div>

                <div class="account">
                    <div class="material-symbols-outlined accountCircle" onclick="toggleDropdown()">account_circle</div>
                    
                    <?php
                        session_start();

                        if (isset($_SESSION['userLoggedIn']) || isset($_SESSION['loggedin'])) {
                            $firstName = $_SESSION['firstName'];

                            $dropdown = "
                                <p class='dropdownOption'>$firstName</p>
                                <hr class='dropdownDivider'>
                                <a class='dropdownOption' href='admin/logout.php'>Odhlásit se</a>
                            ";
                        }
                        

                        else {
                            $dropdown = "
                                <a class='dropdownOption' href='/userLogin.php'>Přihlásit se</a>
                            ";
                        }
                    ?>

                    <div class="accountDropdown" id="accountDropdown">
                        <?php echo $dropdown ?>
                    </div>
                </div>
                
            </div>
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
                        <th class="sideColumn">Autor</th>
                        <th class="mainColumn">Název</th>
                        <th class="sideColumn">Stav</th>
                    </tr>
                </thead>

                <tbody id="tableBody">
                    <?php
                        try {
                            $conn = mysqli_connect('localhost', $sqlUser, $sqlPassword, $database);
                        }
                        
                        catch (mysqli_sql_exception $e) {
                            showError('Chyba připojení', 'Nastala chyba připojení k databázi, zkuste to prosím později.');
                        }

    
                        $sql = "SELECT id, author, name, returnDate, reservation, reservationExpiration FROM books WHERE discarded=0 LIMIT 50";
                        $result = mysqli_query($conn, $sql);
    
                        if ($result === false) {
                            showError('Chyba databáze', 'Nastala chyba čtení dat z databáze, zkuste to prosím později.');
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
                            $reservationExpiration = $data[$i]['reservationExpiration'];

                            $state = 'V knihovně';


                            if ($returnDate != '') {
                                if ($reservation != '') { // lent + reserved
                                    $availableDate = date('Y-m-d', strtotime($returnDate . ' +3 days'));

                                    //change date format
                                    $dateTimeObj = new DateTime($availableDate);
                                    $availableDate = $dateTimeObj->format('j. n.');

                                    $state = "Rezervováno do $availableDate";
                                }

                                else { // lent
                                    //change date format
                                    $dateTimeObj = new DateTime($returnDate);
                                    $returnDate = $dateTimeObj->format('j. n.');

                                    $state = "Půjčeno do $returnDate";
                                } 
                            }

                            else {
                                if ($reservation != '') { // reserved
                                    //change date format
                                    $dateTimeObj = new DateTime($reservationExpiration);
                                    $reservationExpiration = $dateTimeObj->format('j. n.');

                                    $state = "Rezervováno do " . $reservationExpiration;
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


        // account dropdown

        // load dropdown
        document.addEventListener('DOMContentLoaded', function() {
            toggleDropdown();
        });

        function toggleDropdown() {
            let dropdown = document.getElementById("accountDropdown");

            if (dropdown.style.display === "none") {
                dropdown.style.display = "block";
                document.addEventListener('click', closeDropdownOnClickOutside);
                console.log('show');
            } 
            
            else {
                dropdown.style.display = "none";
                document.removeEventListener('click', closeDropdownOnClickOutside);
                console.log('hide');
            }

            event.stopPropagation()
        }


        function closeDropdownOnClickOutside(event) {
            var dropdown = document.getElementById("accountDropdown");
            var targetElement = event.target;
            console.log('out');

            if (!dropdown.contains(targetElement)) {
                dropdown.style.display = "none";
                document.removeEventListener('click', closeDropdownOnClickOutside);
            }
        }
    </script>
</body>
</html>