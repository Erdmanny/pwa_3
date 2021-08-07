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



<nav class="navbar navbar-light bg-light sticky-top">
    <a class="navbar-brand" href="/people">PWA 2</a>
</nav>


<?php
$validation = \Config\Services::validation();
?>

<div class="container">
    <h1 class="mt-3 text-light">Login</h1>
    <div class="row mt-3">

        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <form action="http://localhost/login" method="post">
                        <div class="row m-3">
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="email">Email</label>
                                    <input type="text" class="form-control" name="email" id="email">
                                </div>
                            </div>
                            <?php
                            if ($validation->getError('email')):
                                ?>
                                <div class="col-12">
                                    <div class="alert alert-danger" role="alert">
                                        <?= $validation->getError('email') ?>
                                    </div>
                                </div>
                            <?php
                            endif;
                            ?>
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="password">Password</label>
                                    <input type="password" class="form-control" name="password" id="password" value="">
                                </div>
                            </div>
                            <?php
                            if ($validation->getError('password')):
                                ?>
                                <div class="col-12">
                                    <div class="alert alert-danger" role="alert">
                                        <?= $validation->getError('password') ?>
                                    </div>
                                </div>
                            <?php
                            endif;
                            ?>
                            <div class="col-4">
                                <button type="submit" class="btn btn-warning">Login</button>
                            </div>
                            <div class="col-8 text-right mt-1">
                                <a href="/registration">No account yet?</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!--JQuery JS-->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"
        integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>

<!--Popper JS-->
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"></script>

<!--Bootstrap JS-->
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js"></script>

<!--Bootstrap-Table JS-->
<script src="https://unpkg.com/bootstrap-table@1.18.0/dist/bootstrap-table.min.js"></script>

<!--Bootstrap-Table-Mobile JS-->
<script src="https://unpkg.com/bootstrap-table@1.18.1/dist/extensions/mobile/bootstrap-table-mobile.min.js"></script>


</body>
</html>