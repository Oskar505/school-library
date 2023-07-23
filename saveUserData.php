<?php

    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);







    //$dn = "OU=NG,OU=GYM,OU=Zaci,OU=Uzivatele,DC=GYKOVY,DC=LOCAL"; nizsi gym
    //$dn = "OU=VG,OU=GYM,OU=Zaci,OU=Uzivatele,DC=GYKOVY,DC=LOCAL"; vyssi gym
    //$dn = "OU=SOS,OU=Zaci,OU=Uzivatele,DC=GYKOVY,DC=LOCAL"; soska
    //$dn = 'OU=Uzivatele, DC=GYKOVY,DC=LOCAL'; uzivatele celkove

    $dnList = ["OU=NG,OU=GYM,OU=Zaci,OU=Uzivatele,DC=GYKOVY,DC=LOCAL", "OU=VG,OU=GYM,OU=Zaci,OU=Uzivatele,DC=GYKOVY,DC=LOCAL", "OU=SOS,OU=Zaci,OU=Uzivatele,DC=GYKOVY,DC=LOCAL", "OU=Zamestnanci,OU=Uzivatele,DC=GYKOVY,DC=LOCAL"];

    // get data from all dns in dnList
    for ($i = 0; $i < count($dnList); $i++) {
        $data = getUsers($dnList[$i]);
        updateDb($data);
    }
    

    function getUsers($dn) {
        // get secrets
        require('/var/secrets.php');

        $sqlUser = $secrets['sql-user'];
        $sqlPassword = $secrets['sql-password'];
        $adPassword = $secrets['ad-password'];
        
        $data = [];

        $attributes = array("givenName", "sn", "sAMAccountName");       

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

                $firstName = $users[$i]['givenname'][0];
                $lastName = $users[$i]['sn'][0];
                $login = $users[$i]['samaccountname'][0];
            
                if (empty($firstName) || empty($lastName) || empty($login)) {
                    continue; // values are empty, skip
                }

                
                // class
                $class = getClass($login);

                

                $count ++;

                /*
                echo $count;
                echo'<br>';
                echo "Uživatel: $lastName $firstName, Uživatelské jméno: $login, třída: $class<br />";*/

                array_push($data, array('firstName'=>$firstName, 'lastName'=>$lastName, 'login'=>$login, 'class'=>$class));
            }
        } 
        
        else {
            echo "Hledání v AD selhalo.";
        }


        
        /*
        $result = ldap_search($ad, $dn, '', $attributes);

        $users = ldap_get_entries($ad, $result);

        for ($i=0; $i<$users["count"]; $i++){
            echo $users[$i]["displayname"][0]."(".$users[$i]["l"][0].")<br />";
        }
        */


        ldap_unbind($ad);

        return $data;
    }


    function updateDb($data) {
        // get secrets
        require('/var/secrets.php');

        $sqlUser = $secrets['sql-user'];
        $sqlPassword = $secrets['sql-password'];
        $adPassword = $secrets['ad-password'];



        $conn = mysqli_connect('localhost', $sqlUser, $sqlPassword, 'knihovna');

        if (!$conn) {
            echo 'chyba pripojeni'.mysqli_connect_error();
        }


        for($i = 0; $i < count($data); $i ++) {
            // define
            $firstName = $data[$i]['firstName'];
            $lastName = $data[$i]['lastName'];
            $login = $data[$i]['login'];
            $class = $data[$i]['class'];


            // get count
            $sql = "SELECT COUNT(*) as count FROM users WHERE login = '$login'";
            $result = mysqli_query($conn, $sql);

            if ($result === false) {
                echo 'Error: '.mysqli_error($conn);
            }

            $count = mysqli_fetch_assoc($result)['count'];


            if ($count == 0) { // if user is not in db
                $stmt = mysqli_prepare($conn, "INSERT INTO users (firstName, lastName, login, class) VALUES (?, ?, ?, ?)");
                mysqli_stmt_bind_param($stmt, "ssss", $firstName, $lastName, $login, $class);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
            }

            else {
                $sql = "SELECT class FROM users WHERE login = '$login'";
                $result = mysqli_query($conn, $sql);

                if ($result === false) {
                    echo 'Error: '.mysqli_error($conn);
                }

                $dbClass = mysqli_fetch_assoc($result);

                /*
                echo $login;
                echo '<br>';
                print_r($dbClass);
                echo '<br>';
                */

                
                if ($class != $dbClass) {
                    $stmt = mysqli_prepare($conn, "UPDATE users SET class = ? WHERE login = ?");
                    mysqli_stmt_bind_param($stmt, "ss", $class, $login);
                    mysqli_stmt_execute($stmt);
                    mysqli_stmt_close($stmt);
                }
            }
        }
    }



    function getClass($login) {

        // osmiletej nebo ctyrletej
        $fourYearStudent = true;

        if (substr($login, 2, 1) == 'x' || substr($login, 2, 1) == 'y') {
            $fourYearStudent = false;
        }

        // years
        $class = '';
        $year = date('Y');
        $yearNum = intval(substr($year, -1));
        $endYearNum = intval(substr($login, 1));

        $endYearNum == 0 ? $endYearNum = 10 : ''; // pokud je 2030 tak aby tam nebyla 0 ale 10

        $today = new DateTime();
        
        // prazdniny
        $targetMonth = 7;
        $targetDay = 1;


        $currentMonth = (int) $today->format('n');  // Získání aktuálního měsíce jako číslo
        $currentDay = (int) $today->format('j');  // Získání aktuálního dne jako číslo

        //$yearNum--;

        if ($currentMonth < $targetMonth || ($currentMonth == $targetMonth && $currentDay < $targetDay)) {
            $yearNum--; // druhe pololeti, novy rok ale ne novy skolni rok
        }

        $remainingYears = $endYearNum - $yearNum;
        
        if ($fourYearStudent) {
            $classNum = 5 - $remainingYears;
        }

        $fourYearStudent ? $classNum = 5 - $remainingYears : $classNum = 9 - $remainingYears;

        
        // class

        $eightYearGrades = ['P', 'S', 'T', 'K', 'Q', 'X', 'M', 'O']; // zkratky rocniku na osmiletym


        if (!preg_match("/\d/", $login)) {
            $classType = 'Zaměstnanec';
            $class = $classType;
        }

        elseif (substr($login, 2, 1) == 'u') { // A na ctyrletym
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
        }
        

        return $class;
    }
?>