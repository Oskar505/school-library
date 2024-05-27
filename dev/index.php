<?php
    // get secrets
    require('/var/secrets.php');

    $sqlUser = $secrets['sql-user'];
    $sqlPassword = $secrets['sql-password'];
    $database = $secrets['sql-database'];


    include '/var/www/html/functions.php';


    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
?>




<!DOCTYPE html>
<html lang="cs" class="scrollSnapContainer">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Knihovna</title>

    <link rel="stylesheet" href="stylesBlue.css">
    <link rel="stylesheet" href="mediaQueries.css">

    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@900&family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">

    <script defer src="script.js"></script>
    <script defer src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
</head>
<body class="indexBody">
    <div class="support-scrollsnap"></div>

    <header class="indexHeader container scrollSnap hidden indexHeaderHidden" id="indexHeader">
        <nav class="glass">
            <h2>Knihovna GYKOVY</h2>

            <div>
                <div class="account" title="Účet">
                    <a href="/userLogin.php">
                        <svg xmlns="http://www.w3.org/2000/svg" height="40px" viewBox="0 -960 960 960" width="40px" fill="#FFFFFF" class="accountCircle" id="accountCircle">
                            <path d="M226-262q59-42.33 121.33-65.5 62.34-23.17 132.67-23.17 70.33 0 133 23.17T734.67-262q41-49.67 59.83-103.67T813.33-480q0-141-96.16-237.17Q621-813.33 480-813.33t-237.17 96.16Q146.67-621 146.67-480q0 60.33 19.16 114.33Q185-311.67 226-262Zm253.88-184.67q-58.21 0-98.05-39.95Q342-526.58 342-584.79t39.96-98.04q39.95-39.84 98.16-39.84 58.21 0 98.05 39.96Q618-642.75 618-584.54t-39.96 98.04q-39.95 39.83-98.16 39.83ZM480.31-80q-82.64 0-155.64-31.5-73-31.5-127.34-85.83Q143-251.67 111.5-324.51T80-480.18q0-82.82 31.5-155.49 31.5-72.66 85.83-127Q251.67-817 324.51-848.5T480.18-880q82.82 0 155.49 31.5 72.66 31.5 127 85.83Q817-708.33 848.5-635.65 880-562.96 880-480.31q0 82.64-31.5 155.64-31.5 73-85.83 127.34Q708.33-143 635.65-111.5 562.96-80 480.31-80Zm-.31-66.67q54.33 0 105-15.83t97.67-52.17q-47-33.66-98-51.5Q533.67-284 480-284t-104.67 17.83q-51 17.84-98 51.5 47 36.34 97.67 52.17 50.67 15.83 105 15.83Zm0-366.66q31.33 0 51.33-20t20-51.34q0-31.33-20-51.33T480-656q-31.33 0-51.33 20t-20 51.33q0 31.34 20 51.34 20 20 51.33 20Zm0-71.34Zm0 369.34Z"/>
                        </svg>
                    </a>
                    
                    
                    <!-- <?php
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

                    <div class="accountDropdown glass" id="accountDropdown">
                        <?php echo $dropdown ?>
                    </div> -->
                </div>
            </div>
            
        </nav>

        <div class="textGlow" data-text="Co si přečtete?">
            <section class="headerContent">
                <h1 class="mainH1">Co si přečtete?</h1>

                <a href="#searchBar" id="searchBarBtn">
                    <div class="headerSearchBtnWidth"> 
                        <div class="headerSearchBtn">
                            <!-- <span class="material-symbols-outlined">search</span> -->
                            <svg xmlns="http://www.w3.org/2000/svg" height="44" viewBox="0 -960 960 960" width="44"><path d="M784-120 532-372q-30 24-69 38t-83 14q-109 0-184.5-75.5T120-580q0-109 75.5-184.5T380-840q109 0 184.5 75.5T640-580q0 44-14 83t-38 69l252 252-56 56ZM380-400q75 0 127.5-52.5T560-580q0-75-52.5-127.5T380-760q-75 0-127.5 52.5T200-580q0 75 52.5 127.5T380-400Z"/></svg>
                            <p>Hledat</p>
                        </div>
                    </div>
                    
                </a>
            </section>
            
        </div>
    </header>



    <main class="scrollSnap hidden indexMainHidden" id="indexMain">
        <section class="search searchGlow">
            <form action="" method="post">
                <input type="text" name="searchBar" id="searchBar" placeholder="Hledat" class="searchBar">
            </form>

            <section class="results" id="results">
                <?php
                    try {
                        $conn = mysqli_connect('localhost', $sqlUser, $sqlPassword, $database);
                    }
                    
                    catch (mysqli_sql_exception $e) {
                        showError('Chyba připojení', 'Nastala chyba připojení k databázi, zkuste to prosím později.');
                    }


                    $sql = "SELECT id, author, name FROM books WHERE discarded=0 AND note='MATURITNÍ ČETBA' LIMIT 9";
                    $result = mysqli_query($conn, $sql);

                    if ($result === false) {
                        showError('Chyba databáze', 'Nastala chyba čtení dat z databáze, zkuste to prosím později.');
                    }

                    $data = mysqli_fetch_all($result, MYSQLI_ASSOC);



                    for ($i = 0; $i < count($data); $i++) {
                        $id = $data[$i]['id'];
                        $author = $data[$i]['author'];
                        $name = $data[$i]['name'];


                        echo "
                            <div class='result'>
                                <span class='bookInfo'>
                                    <p class='bookName'>$name</p>
                                    <p class='bookAuthor'>$author</p>
                                </span>
                                
                                <a href='moreInfo.php?id=$id'>
                                    <span class='material-symbols-outlined moreIcon'>add</span>
                                </a>
                            </div>
                        ";
                    }
                ?>
            </section>
        </section>
    </main>



    <footer class="scrollSnap hidden" id="indexFooter">
        <div class="footerGlow"></div>
        <section class="footerContent">
            <h3>Školní knihovna GYKOVY</h3>
            <p><a href="https://www.gykovy.cz/">Webová stránka GYKOVY</a></p>
            <p>knihovna@gykovy.cz</p>
            <p>Oskar Tvrďoch</p>
        </section>
    </footer>


    



    <!-- <script src="/scripts/accountDropdown.js"></script> -->

    <script>
        // let infoOkBtn = document.getElementsByClassName('infoOkBtn')[0];
        // let infoBox = document.getElementsByClassName('infoBox')[0];

        // infoOkBtn.addEventListener('click', function () {
        //     infoBox.style.display = 'none';
        // })



        function openMoreInfo(id, name) {
            window.location.href = 'moreInfo.php?id=' + id;
        }



        // search
        let searchInput = document.getElementById('searchBar');
        searchInput.addEventListener('input', search);



        function search() {
            searchInput = document.getElementById('searchBar');


            $.ajax({
                url: '/getDataForAjax/getSearchedDataUserNew.php',
                type: 'GET',
                data: {
                    searchInput: searchInput.value,
                },
                success: function(response) {
                    var tbody = $('#results');
                    tbody.html(response);
                }
            });
        }
    </script>
</body>
</html>