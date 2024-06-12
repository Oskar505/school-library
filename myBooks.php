<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Moje knihy</title>

    <link rel="stylesheet" href="stylesBlue.css">
    <link rel="stylesheet" href="mediaQueries.css">

    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@900&family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">


    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@48,400,0,0" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body class="longPage myBooksBody" id="myBooksTop">
    <div id="top"></div>

    <?php include_once 'nav.php'?>



    <main class="myBooksMain container" id="myBooksMain">
        <section class="reserved" id="myBooksReserved">
            <h2>Rezervováno</h2>


            <table id="reservedTable">
                <thead>
                    <tr>
                        <th>Název</th>
                        <th>Do</th>
                    </tr>
                </thead>

                <tbody id="reservedTbody">
                    <tr class="tableSplit"></tr>
                </tbody>
            </table>
        </section>



        <section class="borrowed" id="myBooksBorrowed">
            <h2>Půjčeno</h2>


            <table id="borrowedTable">
                <thead>
                    <tr>
                        <th>Název</th>
                        <th>Do</th>
                    </tr>
                </thead>

                <tbody id="borrowedTbody">
                    <tr class="tableSplit"></tr>
                </tbody>
            </table>
        </section>

        <a href="#top" class="scrollUpLink" id="scrollUpBtn">
            <svg class="scrollUpIcon" xmlns="http://www.w3.org/2000/svg" height="48px" viewBox="0 -960 960 960" width="48px" fill="#E4E2E9">
                <path d="M480-344 240-584l43-43 197 197 197-197 43 43-240 240Z"/>
            </svg>
        </a>
    </main>


    <script src="/scripts/myBooks.js"></script>
</body>
</html>