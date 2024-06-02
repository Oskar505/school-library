<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Moje knihy</title>

    <link rel="stylesheet" href="styles.css">

    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@48,400,0,0" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <header class="header container">
        <h1 class="h1">Moje knihy</h1>


        <div class="userBar">
            <a href="/index.php">
                <div class="material-symbols-outlined homeIcon">home</div>
            </a>

            <div class="account">
                <div class="material-symbols-outlined accountCircle" id="accountCircle">account_circle</div>
                
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
                            <a class='dropdownOption' href='/login.php'>Přihlásit se</a>
                        ";
                    }
                ?>

                <div class="accountDropdown" id="accountDropdown">
                    <?php echo $dropdown ?>
                </div>
            </div>
        </div>



        <nav>
            <!-- <a href="">lorem</a>
            <a href="">ipsum</a>
            <a href="">dolor</a>
            <a href="">sit</a>
            <a href="">amet</a> -->
        </nav>
    </header>



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
    </main>


    <script src="/scripts/accountDropdown.js"></script>
    <script src="/scripts/myBooks.js"></script>
</body>
</html>