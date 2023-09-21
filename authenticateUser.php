<?php
    session_start();

    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    if (isset($_POST['username']) && isset($_POST['password'])) {
      $username = $_POST['username'];
      $password = $_POST['password'];

      if (authUser($username, $password)) {
        // password ok

        // get secrets
        require('/var/secrets.php');

        $sqlUser = $secrets['sql-user'];
        $sqlPassword = $secrets['sql-password'];
        $database = $secrets['sql-database'];


        $conn = mysqli_connect('localhost', $sqlUser, $sqlPassword, $database);

        if (!$conn) {
          echo 'chyba pripojeni'.mysqli_connect_error();
        }


        $sql = "SELECT firstName, lastName, class, reserved, borrowed FROM users WHERE login='$username'";
        $result = mysqli_query($conn, $sql);

        if ($result === false) {
          echo 'Error: '.mysqli_error($conn);
        }

        $data = mysqli_fetch_assoc($result);

        $firstName = $data['firstName'];
        $lastName = $data['lastName'];
        $class = $data['class'];
        $reserved = $data['reserved'];
        $borrowed = $data['borrowed'];

        if ($class === 'Admin' || $username === 'admin') { 
          session_regenerate_id();
          $_SESSION['loggedin'] = TRUE;
          $_SESSION['login'] = $username;
          $_SESSION['firstName'] = $firstName . ' (admin)';
          $_SESSION['lastName'] = $lastName;
          $_SESSION['id'] = $id;
          $_SESSION['reserved'] = $reserved;
          $_SESSION['borrowed'] = $borrowed;


          header('Location: /admin');
          exit;
        }

        else {
          session_regenerate_id();
          $_SESSION['userLoggedIn'] = TRUE;
          $_SESSION['login'] = $username;
          $_SESSION['firstName'] = $firstName;
          $_SESSION['lastName'] = $lastName;
          $_SESSION['id'] = $id;
          $_SESSION['reserved'] = $reserved;
          $_SESSION['borrowed'] = $borrowed;


          // redirect to index
          header('Location: index.php');
          exit;
        }
      }

      else {
        echo '<h1>Nesprávné přihlašovací údaje</h1>';
      }
    }


    else {
      echo '<h1>Vyplňte prosím přihlašovací údaje</h1>';
    }


    



  function authUser($user, $psw) {
    // get secrets
    require('/var/secrets.php');

    $sqlUser = $secrets['sql-user'];
    $sqlPassword = $secrets['sql-password'];
    $database = $secrets['sql-database'];



    $con = mysqli_connect('localhost', $sqlUser, $sqlPassword, 'knihovna');


    if ( mysqli_connect_errno() ) {
        // error
        exit('Nepodařilo se připojit k databázi: ' . mysqli_connect_error());
    }




    if (empty($user) || empty($psw)) return false; // empty
  
    //LDAP server address
    $ldap_server = "192.168.100.20";
    $ldap_serverz = "192.168.100.22";
  
    //domain user to connect to LDAP
    $ldap_user = $user."@gykovy.local";
  
    //user password
    $ldap_psw = $psw;
  
    //path
    $ldap_dn = "DC=gykovy,DC=local";
  
    $ldap = ldap_connect($ldap_server);
        
        
          
    if($r=@ldap_bind($ldap,$ldap_user,$ldap_psw)) { 
      // valid
      return true;
    } 
    
    else {
      // backup server
      $ldap = ldap_connect($ldap_serverz);

      if ($r=@ldap_bind($ldap,$ldap_user,$ldap_psw)) {
        return true; // backup server valid
      }

      elseif ($stmt = $con->prepare('SELECT id, password FROM adminLogin WHERE username = ?')) {
        $stmt->bind_param('s', $_POST['username']);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
          $stmt->bind_result($id, $password);
          $stmt->fetch();
          // Account exist

          if (password_verify($_POST['password'], $password)) {
            return true;
          }

        }
      
        else {
          return false;
        }


        $stmt->close();
      }

      else {
        return false;
      }
    }
  }
?>