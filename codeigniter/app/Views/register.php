<?php
$validation = \Config\Services::validation();
?>

<div class="container">
    <h1 class="mt-3 text-light">Register</h1>
    <div class="row mt-3">


        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <form action="/register" method="post">
                        <div class="row m-3">
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="email">Email</label>
                                    <input type="text" class="form-control" name="email" id="email" placeholder="Please enter a valid email">
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
                                    <label for="token">Token</label>
                                    <input type="text" class="form-control" name="token" id="token" placeholder="Please enter a token">
                                    <div class="form-text" id="formTextPassword">Token must be of length 4</div>
                                </div>
                            </div>
                            <?php
                            if ($validation->getError('token')):
                                ?>
                                <div class="col-12">
                                    <div class="alert alert-danger" role="alert">
                                        <?= $validation->getError('token') ?>
                                    </div>
                                </div>
                            <?php
                            endif;
                            ?>
                            <div class="col-12 col-sm-6">
                                <div class="form-group">
                                    <label for="password">Password</label>
                                    <input type="password" class="form-control" name="password" id="password" placeholder="Enter password" aria-describedby="formTextPassword">
                                    <div class="form-text" id="formTextPassword">Password must have more than 8 signs</div>
                                </div>
                            </div>
                            <div class="col-12 col-sm-6">
                                <div class="form-group">
                                    <label for="password_confirm">Confirm Password</label>
                                    <input type="password" class="form-control" name="password_confirm"
                                           id="password_confirm" placeholder="Repeat password">
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
                            <?php
                            if ($validation->getError('password_confirm')):
                                ?>
                                <div class="col-12">
                                    <div class="alert alert-danger" role="alert">
                                        <?= $validation->getError('password_confirm') ?>
                                    </div>
                                </div>
                            <?php
                            endif;
                            ?>
                            <div class="col-4">
                                <button type="submit" class="btn btn-warning">Register</button>
                            </div>
                            <div class="col-8 text-right mt-1">
                                <a href="/">Login</a>
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
