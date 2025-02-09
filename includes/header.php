<?php
require_once 'config/database.php';
require_once 'auth/functions.php';

if (!isLoggedIn()) {
    header("Location: auth/login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Pakar - <?= $page_title ?? 'Home' ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body class="bg-gray-100"></body>