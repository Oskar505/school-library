<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Přihlášení</title>

    <link rel="stylesheet" href="stylesBlue.css">
    <link rel="stylesheet" href="mediaQueries.css">

    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@900&family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">

</head>
<body class="container">
    <?php include_once 'nav.php'?>



    <main class="loginContainer container">
        <?php 
            session_start();


            $redirect = '';

            if (isset($_GET['redirect'])) {
                $redirect = $_GET['redirect'];
            }

            echo $redirect;


            if (isset($_SESSION['userLoggedIn']) || isset($_SESSION['loggedin'])) {
                echo '
                    <h1 class="loginHeading">Odhlásit se</h1>

                    <form action="authenticateUser.php?redirect=' . $redirect . '" autocapitalize="off" method="post" class="loginForm">
                        <input type="submit" class="loginBtn" value="Odhlásit se" name="logout">
                    </form>
                ';
            }


            else {
                echo '
                    <h1 class="loginHeading">Přihlaste se</h1>

                    <form action="authenticateUser.php?redirect=' . $redirect . '" autocapitalize="off" method="post" class="loginForm">
                        <input type="text" name="username" placeholder="Uživatelské jméno" id="username" class="loginInput" required>
                        <input type="password" name="password" placeholder="Heslo" id="password" class="loginInput" required>
            
                        <p class="loginNote">Použijte přihlašovací údaje do moodlu</p>
            
                        <input type="submit" class="loginBtn" value="Přihlásit se">
                    </form>
                ';
            }
        
        
        ?>
        <!-- <h1 class="loginHeading">Přihlaste se</h1>

        <form action="authenticateUser.php" autocapitalize="off" method="post" class="loginForm">
            <input type="text" name="username" placeholder="Uživatelské jméno" id="username" class="loginInput" required>
            <input type="password" name="password" placeholder="Heslo" id="password" class="loginInput" required>

            <p class="loginNote">Použijte přihlašovací údaje do moodlu</p>

            <input type="submit" class="loginBtn" value="Přihlásit se">
        </form> -->
        
        

        
    </main>
    
</body>
</html>