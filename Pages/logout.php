<?php
session_start();
$_SESSION['login']=0;
header('Location: ../home');
?>