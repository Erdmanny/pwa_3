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
                document.getElementById("edit-surname").value = person["name"];
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
                document.getElementById("edit-surname").value = person["name"];
                document.getElementById("edit-street").value = person["street"];
                document.getElementById("edit-postcode").value = person["zip"];
                document.getElementById("edit-city").value = person["city"];
            });
    });






</script>


