<?php
session_start();
session_destroy();

unset($_SESSION['name']);
unset($_SESSION['user_id']);
unset($_SESSION['profile_id']);
unset($_SESSION['first_name']);
header('Location: index.php');
?>