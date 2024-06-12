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
<html lang="cs" class="">
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

    <script defer src="scripts/indexScript.js"></script>
    <script defer src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
</head>
<body class="indexBody">
    <div class="scrollSnapContainer">
        <div class="support-scrollsnap"></div>

        <header class="indexHeader container scrollSnap hidden indexHeaderHidden" id="indexHeader">
            <?php include_once 'nav.php'?>

            <div class="textGlow" data-text="Co si přečtete?">
                <section class="headerContent">
                    <h1 class="mainH1">Co si přečtete?</h1>

                    <a href="#searchBar" id="searchBarBtn">
                        <div class="headerSearchBtnWidth" id="searchBarBtn"> 
                            <div class="headerSearchBtn">
                                <!-- <span class="material-symbols-outlined">search</span> -->
                                <svg xmlns="http://www.w3.org/2000/svg" height="44" viewBox="0 -960 960 960" width="44"><path d="M784-120 532-372q-30 24-69 38t-83 14q-109 0-184.5-75.5T120-580q0-109 75.5-184.5T380-840q109 0 184.5 75.5T640-580q0 44-14 83t-38 69l252 252-56 56ZM380-400q75 0 127.5-52.5T560-580q0-75-52.5-127.5T380-760q-75 0-127.5 52.5T200-580q0 75 52.5 127.5T380-400Z"/></svg>
                                <p>Hledat</p>
                            </div>
                        </div>
                        
                    </a>
                </section>
                
            </div>

            <a href="#searchBar" class="searchLink">
                <svg class="scrollDownIcon" xmlns="http://www.w3.org/2000/svg" height="48px" viewBox="0 -960 960 960" width="48px" fill="#E4E2E9">
                    <path d="M480-344 240-584l43-43 197 197 197-197 43 43-240 240Z"/>
                </svg>
            </a>
            

            <!-- <svg width="46" height="89" viewBox="0 0 46 89" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M32.7425 66L22.8712 75.9571L13 66" stroke="white" stroke-width="5" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M32.8713 76.5L23 86.4571L13.1288 76.5" stroke="white" stroke-width="5" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M3 38.3736C3 43.4252 5.10726 48.2703 8.85797 51.8422C12.6087 55.4144 17.6957 57.4206 23 57.4206C28.3043 57.4206 33.3914 55.4144 37.1423 51.8422C40.8929 48.2703 43 43.4252 43 38.3736V22.0473C43 16.9956 40.8929 12.151 37.1423 8.57893C33.3914 5.00688 28.3043 3 23 3C17.6957 3 12.6087 5.00688 8.85797 8.57893C5.10726 12.151 3 16.9956 3 22.0473V38.3736Z" stroke="white" stroke-width="5" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M22.9115 18.7241L22.9497 16.2936L22.9585 13.9874" stroke="white" stroke-width="5" stroke-linecap="round" stroke-linejoin="round"/>
            </svg> -->


        </header>



        <main class="scrollSnap hidden indexMainHidden" id="indexMain">
            <section class="search searchGlow">
                <form action="" method="post" onkeydown="return event.key != 'Enter';" autocomplete="off">
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
                                <div class='outerResult'>
                                    <a href='moreInfo.php?id=$id'>
                                        <div class='result'>
                                            <span class='bookInfo'>
                                                <p class='bookName'>$name</p>
                                                <p class='bookAuthor'>$author</p>
                                            </span>
                                            
                                            <span class='material-symbols-outlined moreIcon'>add</span>
                                        </div>
                                    </a>
                                </div>
                                
                            ";
                        }
                    ?>
                </section>

                <section class="mainButtons">
                    <a href="">
                        <div class="mainBtn" id="moreBtn">
                            <p>Více</p>
                        </div>
                    </a>
                
                    <a href="">
                        <div class="mainBtn" id="gradBtn">
                            <p>Maturitní četba</p>
                        </div>
                    </a> 
                </section>
                
            </section>
        </main>



        <footer class="scrollSnap indexFooterHidden" id="indexFooter">
            <div class="footerGlow"></div>
            <section class="footerContent">
                <h3>Školní knihovna GYKOVY</h3>
                <p><a href="https://www.gykovy.cz/">Webová stránka GYKOVY</a></p>
                <p>knihovna@gykovy.cz</p>
                <p>Oskar Tvrďoch</p>
            </section>
        </footer>
    </div>

    



    

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



        // smooth scroll
        let searchBtn = document.getElementById('searchBarBtn');

        console.log(searchBtn);
        console.log('test');

        searchBtn.addEventListener('click', function() {
            console.log('click')
            window.scrollBy(0,2000);
        });
    </script>
</body>
</html>