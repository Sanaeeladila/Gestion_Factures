<?php //On détruit la session
    session_start();
    $_SESSION = array();
    session_destroy();
    header("Location: login.php");
?>