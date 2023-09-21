<?php
    function showError($error, $errorMessage, $link = '/index.php', $warning = false) {
        session_start();

        $_SESSION['error'] = $error;
        $_SESSION['errorMessage'] = $errorMessage;
        $_SESSION['link'] = $link;
        $_SESSION['warning'] = $warning;

        header("Location: /error.php");
        exit();
    }


    function getWhere() {
        // get cookies, if cookie doesn't exist set it to ''
        $searchBy = isset($_COOKIE['searchBy']) ? $_COOKIE['searchBy'] : '';
        $showDiscarded = isset($_COOKIE['showDiscarded']) ? $_COOKIE['showDiscarded'] : 'false';
        $searchInput = isset($_COOKIE['searchInput']) ? $_COOKIE['searchInput'] : '';


        if ($showDiscarded == 'true') {
            if ($searchBy == '') {
                $where = "";
            }
    
            else {
                $where = "WHERE $searchBy";
            }
        }
    
        else {
            if ($searchBy == '') {
                $where = "WHERE discarded=0";
            }
    
            else {
                $where = "WHERE ($searchBy) AND discarded=0";
            }     
        }


        return $where;
    }
?>