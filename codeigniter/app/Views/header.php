<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="shortcut icon" type="image/x-icon" href="logo.ico">
    <link rel="manifest" href="manifest.webmanifest">
    <title>PWA 2</title>
    <meta name="theme-color" content="#FFE1C4">

    <link rel="apple-touch-icon" href="icon/icon96.png">
    <meta name="apple-mobile-web-app-status-bar" content="#aa7700">


    <!--Bootstrap CSS-->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css">
    <!--Bootstrap-Table CSS-->
    <link rel="stylesheet" href="https://unpkg.com/bootstrap-table@1.18.0/dist/bootstrap-table.min.css">
    <!--    Bootstrap Icons-->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css">
</head>

<body class="bg-dark">

<?php
$session = \Config\Services::session();
?>

<nav class="navbar navbar-light bg-light sticky-top">
    <a class="navbar-brand" href="/people">PWA 1</a>
    <div class="ml-auto d-flex">
        <button id="pushButton" class="btn btn-primary mr-2 d-flex justify-content-center align-items-center">Allow Push</button>
        <a href="/logout" class="btn btn-warning mr-2">Logout</a>
        <div id="show-online" class="bg-success d-flex justify-content-center align-items-center p-2">
            Online
        </div>
    </div>
</nav>

<script type="application/javascript">
    if (!navigator.onLine){
        document.getElementById("pushButton").disabled = true;
    }
</script>