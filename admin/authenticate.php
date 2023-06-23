<?php
    session_start();

    require('/var/secrets.php');
    $sqlUser = $secrets['sql-user'];
    $sqlPassword = $secrets['sql-password'];
    $database = $secrets['sql-database'];


    $con = mysqli_connect('localhost', $sqlUser, $sqlPassword, 'knihovna');


    if ( mysqli_connect_errno() ) {
        // error
        exit('Nepodařilo se připojit k databázi: ' . mysqli_connect_error());
    }

    // if login data was submitted
    if ( !isset($_POST['username'], $_POST['password']) ) {
        exit('Vyplňte prosím všecny políčka.');
    }

    // SQL
    if ($stmt = $con->prepare('SELECT id, password FROM adminLogin WHERE username = ?')) {
        $stmt->bind_param('s', $_POST['username']);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($id, $password);
            $stmt->fetch();
            // Account exist
            if (password_verify($_POST['password'], $password)) {
                // password ok
                session_regenerate_id();
                $_SESSION['loggedin'] = TRUE;
                $_SESSION['name'] = $_POST['username'];
                $_SESSION['id'] = $id;

                // redirect to admin site
                header('Location: index.php');
                exit;
            } 
            
            else {
                // Incorrect password
                echo 'Nesprávné přihlašovací údaje!';
            }
        } 
        
        else {
            // Incorrect username
            echo 'Nesprávné přihlašovací údaje!';
        }

        $stmt->close();
    }
?>