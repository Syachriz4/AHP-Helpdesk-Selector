<?php
session_start();

$_SESSION['user'] = "User 1";

header("Location: index.php");
