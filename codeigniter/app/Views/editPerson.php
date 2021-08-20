<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="shortcut icon" type="image/x-icon" href="/logo.ico">
    <link rel="manifest" href="/manifest.webmanifest">
    <title>PWA 3</title>
    <meta name="theme-color" content="#FFE1C4">

    <link rel="apple-touch-icon" href="/icon/icon96.png">
<!--    <meta name="apple-mobile-web-app-status-bar" content="#aa7700">-->


    <!--Bootstrap CSS-->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css">
    <!--Bootstrap-Table CSS-->
    <link rel="stylesheet" href="https://unpkg.com/bootstrap-table@1.18.0/dist/bootstrap-table.min.css">
    <!--    Bootstrap Icons-->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css">
</head>

<body class="bg-dark">


<nav class="navbar navbar-light bg-light sticky-top">
    <a class="navbar-brand" href="/people">PWA 3</a>
    <div class="ml-auto d-flex" id="nav-buttons"></div>
</nav>

<script type="application/javascript">
    if (navigator.onLine) {
        document.getElementById("nav-buttons").innerHTML = "<button id='pushButton' class='btn btn-primary mr-2 d-flex justify-content-center align-items-center'>Allow Push</button>" +
            "<a href='/logout'><button class='btn btn-warning mr-2'> Logout</button></a>" +
            "<div id='show-online' class='bg-success d-flex justify-content-center align-items-center p-2'>Online </div>"
    } else {
        document.getElementById("nav-buttons").innerHTML = "<button id='pushButton' class='btn btn-primary mr-2 d-flex justify-content-center align-items-center' disabled>Allow Push</button>" +
            "<a href='/logout'><button class='btn btn-warning mr-2' disabled> Logout</button></a>" +
            "<div id='show-online' class='bg-danger d-flex justify-content-center align-items-center p-2'>Offline </div>"
    }


</script>

<div class="container">
    <h1 class="mt-3 text-light">Edit Person</h1>
    <div class="row mt-3">

        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <div class="row m-3">
                        <div class="col-12">
                            <div class="form-group">
                                <label for="edit-prename">Prename:</label>
                                <input type="text" class="form-control" name="edit-prename" id="edit-prename"
                                       placeholder="Peter" value="">
                            </div>
                        </div>
                        <div class="col-12" id="error-edit-prename"></div>


                        <div class="col-12">
                            <div class="form-group">
                                <label for="edit-surname">Surname:</label>
                                <input type="text" class="form-control" name="edit-surname" id="edit-surname"
                                       placeholder="Mustermann" value="">
                            </div>
                        </div>
                        <div class="col-12" id="error-edit-surname"></div>

                        <div class="col-12">
                            <div class="form-group">
                                <label for="edit-street">Street:</label>
                                <input type="text" class="form-control" name="edit-street" id="edit-street"
                                       placeholder="Musterstr. 11" value="">
                            </div>
                        </div>
                        <div class="col-12" id="error-edit-street"></div>

                        <div class="col-12">
                            <div class="form-group">
                                <label for="edit-postcode">Postcode:</label>
                                <input type="text" class="form-control" name="edit-postcode" id="edit-postcode"
                                       placeholder="54299" value="">
                            </div>
                        </div>
                        <div class="col-12" id="error-edit-postcode"></div>

                        <div class="col-12">
                            <div class="form-group">
                                <label for="edit-city">City:</label>
                                <input type="text" class="form-control" name="edit-city" id="edit-city"
                                       placeholder="Musterhausen" value="">
                            </div>
                        </div>
                        <div class="col-12" id="error-edit-city"></div>

                        <div class="col-12">
                            <a href="/people" class="btn btn-warning">Cancel</a>
                            <input type="hidden" name="id" id="edit-id" value="">
                            <button id="edit-button" type="submit" class="btn btn-primary float-right">Edit</button>
                        </div>
                    </div>


                </div>

            </div>
        </div>

    </div>
</div>

<script type="application/javascript">
    let id = window.location.search.substr(1).split("=")[1];
    if (navigator.onLine) {
        people = fetch("http://localhost/people/getPeople")
            .then(response => response.json())
            .then(data => {
                let person = data.find(element => element["id"] === id);
                document.getElementById("edit-id").value = person["id"];
                document.getElementById("edit-prename").value = person["prename"];
                document.getElementById("edit-surname").value = person["surname"];
                document.getElementById("edit-street").value = person["street"];
                document.getElementById("edit-postcode").value = person["zip"];
                document.getElementById("edit-city").value = person["city"];
            })
    }

    // fetch cached data
    caches.open("dynamic-v1").then(function (cache) {
        cache.match("http://localhost/people/getPeople")
            .then(response => {
                if (!response) throw Error("No Data");
                return response.json();
            })
            .then(data => {
                let person = data.find(element => element["id"] === id);
                document.getElementById("edit-id").value = person["id"];
                document.getElementById("edit-prename").value = person["prename"];
                document.getElementById("edit-surname").value = person["surname"];
                document.getElementById("edit-street").value = person["street"];
                document.getElementById("edit-postcode").value = person["zip"];
                document.getElementById("edit-city").value = person["city"];
            });
    });






</script>


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

<script src="/app.js"></script>

</body>
</html>