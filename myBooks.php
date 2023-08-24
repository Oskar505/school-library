<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Moje knihy</title>

    <link rel="stylesheet" href="styles.css">

    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@48,400,0,0" />
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
                            <a class='dropdownOption' href='/userLogin.php'>Přihlásit se</a>
                        ";
                    }
                ?>

                <div class="accountDropdown" id="accountDropdown">
                    <?php echo $dropdown ?>
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



    <main class="myBooksMain container">
        <section class="reserved">
            <h2>Rezervováno</h2>


            <table>
                <thead>
                    <tr>
                        <th>Název</th>
                        <th>Do</th>
                    </tr>
                </thead>

                <tbody>
                    <tr class="tableSplit"></tr>

                    <tr class="myBooksTr">
                        <td>Název knížky</td>
                        <td>25. 9.</td>
                    </tr>

                    <tr class="tableSplit"></tr>

                    <tr class="myBooksTr">
                        <td>Název knížky</td>
                        <td>25. 9.</td>
                    </tr>

                    <tr class="tableSplit"></tr>

                    <tr class="myBooksTr">
                        <td>Název knížky</td>
                        <td>25. 9.</td>
                    </tr>

                    <tr class="tableSplit"></tr>

                    <tr class="myBooksTr">
                        <td>Název knížky</td>
                        <td>25. 9.</td>
                    </tr>
                </tbody>
            </table>
        </section>



        <section class="borrowed">
            <h2>Půjčeno</h2>


            <table>
                <thead>
                    <tr>
                        <th>Název</th>
                        <th>Do</th>
                    </tr>
                </thead>

                <tbody>
                    <tr class="tableSplit"></tr>

                    <tr class="myBooksTr" class="myBooksTr">
                        <td>Název knížky</td>
                        <td>25. 9.</td>
                    </tr>

                    <tr class="tableSplit"></tr>

                    <tr class="myBooksTr">
                        <td>Název knížky</td>
                        <td>25. 9.</td>
                    </tr>

                    <tr class="tableSplit"></tr>

                    <tr class="myBooksTr">
                        <td>Název knížky</td>
                        <td>25. 9.</td>
                    </tr>

                    <tr class="tableSplit"></tr>

                    <tr class="myBooksTr">
                        <td>Název knížky</td>
                        <td>25. 9.</td>
                    </tr>
                </tbody>
            </table>
        </section>
    </main>


    <script src="accountDropdown.js"></script>
</body>
</html>