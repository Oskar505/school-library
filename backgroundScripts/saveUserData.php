<?php
    session_start();

    if (!isset($_SESSION['loggedin'])) {
        header('Location: /login.php');
        exit;
    }

    


    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);


    include_once('sendMail.php');


    // output of this script, this will be sent in mail
    $output = [];



    //$dn = "OU=NG,OU=GYM,OU=Zaci,OU=Uzivatele,DC=GYKOVY,DC=LOCAL"; nizsi gym
    //$dn = "OU=VG,OU=GYM,OU=Zaci,OU=Uzivatele,DC=GYKOVY,DC=LOCAL"; vyssi gym
    //$dn = "OU=SOS,OU=Zaci,OU=Uzivatele,DC=GYKOVY,DC=LOCAL"; soska
    //$dn = 'OU=Uzivatele, DC=GYKOVY,DC=LOCAL'; uzivatele celkove

    $dnList = ["OU=NG,OU=GYM,OU=Zaci,OU=Uzivatele,DC=GYKOVY,DC=LOCAL", "OU=VG,OU=GYM,OU=Zaci,OU=Uzivatele,DC=GYKOVY,DC=LOCAL", "OU=SOS,OU=Zaci,OU=Uzivatele,DC=GYKOVY,DC=LOCAL", "OU=Zamestnanci,OU=Uzivatele,DC=GYKOVY,DC=LOCAL"];

    // MAIN CODE
    // get data from all dns in dnList
    for ($i = 0; $i < count($dnList); $i++) {
        $data = getUsers($dnList[$i]);
        updateDb($data);
    }


    print_r($output);

    // SEND MAIL
    $mail = new SendMail('knihovna');
    $mail->cronJobOutput('saveUserData', $output);
    




    function getUsers($dn) {
        global $output;

        // get secrets
        require('/var/secrets.php');

        $sqlUser = $secrets['sql-user'];
        $sqlPassword = $secrets['sql-password'];
        $adPassword = $secrets['ad-password'];
        
        $data = [];

        $attributes = ["givenName", "sn", "sAMAccountName", "mail"];   

        $ad = ldap_connect("192.168.100.20") or die("Couldn't connect to AD!");
    
        ldap_set_option($ad, LDAP_OPT_PROTOCOL_VERSION, 3);

        $bd = ldap_bind($ad,"sqread@gykovy.local", $adPassword) or die("Couldn't bind to AD!");
        

        $result = ldap_search($ad, $dn, '(objectClass=*)', $attributes);

        if ($result !== false) {
            $users = ldap_get_entries($ad, $result);
            $count = 0;


            for ($i = 0; $i < $users["count"]; $i++) {
                if (!isset($users[$i]['sn'][0]) || !isset($users[$i]['givenname'][0]) || !isset($users[$i]['samaccountname'][0])) {
                    continue; // key is missing, skip
                }

                print_r($users[$i]['mail'][0]);
                echo '<br>';


                $firstName = $users[$i]['givenname'][0];
                $lastName = $users[$i]['sn'][0];
                $login = $users[$i]['samaccountname'][0];
                $userMail = $users[$i]['mail'][0];
            
                if (empty($firstName) || empty($lastName) || empty($login)) {
                    continue; // values are empty, skip
                }

                
                // class
                $class = getClass($login);

                

                $count ++;

                array_push($data, ['firstName'=>$firstName, 'lastName'=>$lastName, 'login'=>$login, 'mail'=>$userMail, 'class'=>$class[0], 'graduate'=>$class[1]]);
            }
        } 
        
        else {
            $output[] = "Hledání v AD selhalo.";
        }



        ldap_unbind($ad);

        return $data;
    }


    function updateDb($data) {
        global $output;

        // get secrets
        require('/var/secrets.php');

        $sqlUser = $secrets['sql-user'];
        $sqlPassword = $secrets['sql-password'];
        $adPassword = $secrets['ad-password'];



        $conn = mysqli_connect('localhost', $sqlUser, $sqlPassword, 'knihovna');

        if (!$conn) {
            $output[] = 'chyba pripojeni'.mysqli_connect_error();
        }


        for($i = 0; $i < count($data); $i ++) {
            // define
            $firstName = $data[$i]['firstName'];
            $lastName = $data[$i]['lastName'];
            $login = $data[$i]['login'];
            $userMail = $data[$i]['mail'];
            $class = $data[$i]['class'];
            $graduate = $data[$i]['graduate'];


            // get count
            $sql = "SELECT COUNT(*) as count FROM users WHERE login = '$login'";
            $result = mysqli_query($conn, $sql);

            if ($result === false) {
                $output[] = 'Error: '.mysqli_error($conn);
            }

            $count = mysqli_fetch_assoc($result)['count'];

            echo "$firstName $lastName, login: $login,  class: $class, mail: $userMail, graduate: $graduate";
            echo '<br>';

            if ($count == 0) { // if user is not in db
                $output[] = "$firstName $lastName, login: $login,  class: $class, mail: $userMail, graduate: $graduate";

                $stmt = mysqli_prepare($conn, "INSERT INTO users (firstName, lastName, login, email, class) VALUES (?, ?, ?, ?, ?)");
                mysqli_stmt_bind_param($stmt, "sssss", $firstName, $lastName, $login, $userMail, $class);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
            }

            else {
                $sql = "SELECT class FROM users WHERE login = '$login'";
                $result = mysqli_query($conn, $sql);

                if ($result === false) {
                    $output[] = 'Error: '.mysqli_error($conn);
                }

                $dbClass = mysqli_fetch_assoc($result);


                
                if ($class != $dbClass && $graduate == false && $class != 'Zaměstnanec') { // next grade
                    $stmt = mysqli_prepare($conn, "UPDATE users SET class = ? WHERE login = ?");
                    mysqli_stmt_bind_param($stmt, "ss", $class, $login);
                    mysqli_stmt_execute($stmt);
                    mysqli_stmt_close($stmt);


                    // update class in books
                    $stmt = mysqli_prepare($conn, "UPDATE books SET class = ? WHERE lentTo = ?");
                    mysqli_stmt_bind_param($stmt, "ss", $class, $login);
                    mysqli_stmt_execute($stmt);
                    mysqli_stmt_close($stmt);
                }


                elseif ($graduate == true) { // graduate
                    $stmt = mysqli_prepare($conn, "UPDATE users SET graduate = 1 WHERE login = ?");
                    mysqli_stmt_bind_param($stmt, "s",  $login);
                    mysqli_stmt_execute($stmt);
                    mysqli_stmt_close($stmt);
                }
            }
        }
    }



    function updateMail($data) {
        global $output;

        // get secrets
        require('/var/secrets.php');

        $sqlUser = $secrets['sql-user'];
        $sqlPassword = $secrets['sql-password'];
        $adPassword = $secrets['ad-password'];



        $conn = mysqli_connect('localhost', $sqlUser, $sqlPassword, 'knihovna');

        if (!$conn) {
            $output[] = 'chyba pripojeni'.mysqli_connect_error();
        }


        for($i = 0; $i < count($data); $i ++) {
            // define
            $firstName = $data[$i]['firstName'];
            $lastName = $data[$i]['lastName'];
            $login = $data[$i]['login'];
            $userMail = $data[$i]['mail'];
            $class = $data[$i]['class'];
            $graduate = $data[$i]['graduate'];


            $output[] = "$firstName $lastName, login: $login,  class: $class, mail: $userMail, graduate: $graduate";
            echo "$firstName $lastName, login: $login,  class: $class, mail: $userMail, graduate: $graduate";
            echo '<br>';


            $stmt = mysqli_prepare($conn, "UPDATE users SET email = ? WHERE login = ?");
            mysqli_stmt_bind_param($stmt, "ss", $userMail, $login);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
        }
    }



    function getClass($login) {
        global $output;


        // osmiletej nebo ctyrletej
        $fourYearStudent = true;

        if (substr($login, 2, 1) == 'x' || substr($login, 2, 1) == 'y') {
            $fourYearStudent = false;
        }

        // years
        $class = '';
        $year = date('Y');
        $yearNum = intval(substr($year, -1)); //3
        $endYearNum = intval(substr($login, 1));//1


        // počítáme rok, který byl v prvním pololetí, pokud je druhé tak odečíst 1 rok
        $targetMonth = 7;
        $targetDay = 1;
        $today = new DateTime();

        $currentMonth = (int) $today->format('n');  // Získání aktuálního měsíce jako číslo
        $currentDay = (int) $today->format('j');  // Získání aktuálního dne jako číslo

        if ($currentMonth < $targetMonth || ($currentMonth == $targetMonth && $currentDay < $targetDay)) {
            $yearNum--; // druhe pololeti, novy rok ale ne novy skolni rok
        }



        if ($endYearNum != $yearNum) { // student ještě není absolvent

            $endYearNum == 0 ? $endYearNum = 10 : ''; // pokud je 2030 tak aby tam nebyla 0 ale 10

            $endYearNum < $yearNum ? $endYearNum = $endYearNum + 10 : '';


            $remainingYears = $endYearNum - $yearNum;


            
            // class
            $eightYearGrades = ['P', 'S', 'T', 'K', 'Q', 'X', 'M', 'O']; // zkratky rocniku na osmiletym
            $graduate = false;


            if (!preg_match("/\d/", $login)) { // staff
                $classType = 'Zaměstnanec';
                $class = $classType;
            }

            elseif ($remainingYears > 0) { // student, not graduate
                // get classNum
                $fourYearStudent ? $classNum = 5 - $remainingYears : $classNum = 9 - $remainingYears;



                if (substr($login, 2, 1) == 'u') { // A na ctyrletym
                    $classType = 'A';
                    $class = $classNum . '.' . $classType;
                }
        
                elseif (substr($login, 2, 1) == 'v') { // B na ctyrletym
                    $classType = 'B';
                    $class = $classNum . '.' . $classType;
                }
        
                elseif (substr($login, 2, 1) == 'x') { // gate
                    $classType = $eightYearGrades[$classNum - 1] . 'A';
                    $class = $classNum . '.' . $classType;
                }
        
                elseif (substr($login, 2, 1) == 'y') { // ozon
                    $classType = $eightYearGrades[$classNum - 1] . 'B';
                    $class = $classNum . '.' . $classType;
                }
        
                elseif (substr($login, 2, 1) == 's') { // sestra
                    $classType = 'PS';
                    $class = $classNum . '.' . $classType;
                }
        
                elseif (substr($login, 2, 1) == 't') { // ekonom
                    $classType = 'AK';
                    $class = $classNum . '.' . $classType;
                }
        
                else {
                    $class = 'Get class error';
                    $output[] = $class;
                }
            }


            else {
                $class = 'Get class error: there should not be graduate.';
                $output[] = $class;
            }
        }



        else {
            $graduate = true;
        }

        
        

        return [$class, $graduate];
    }
?>