<div class="container">
    <h1 class="mt-3 text-light">Add Person</h1>
    <div class="row mt-3">

        <div class="col-lg-12">
            <div class="card">
                <div class="card-body">
                    <div class="row m-3">
                        <div class="col-12">
                            <div class="form-group">
                                <label for="new-prename">Prename:</label>
                                <input type="text" class="form-control" name="new-prename" id="new-prename"
                                       placeholder="Peter">
                            </div>
                        </div>

                        <div class="col-12" id="error-new-prename"></div>

                        <div class="col-12">
                            <div class="form-group">
                                <label for="new-surname">Surname:</label>
                                <input type="text" class="form-control" name="new-surname" id="new-surname"
                                       placeholder="Mustermann">
                            </div>
                        </div>
                        <div class="col-12" id="error-new-surname"></div>

                        <div class="col-12">
                            <div class="form-group">
                                <label for="new-street">Street:</label>
                                <input type="text" class="form-control" name="new-street" id="new-street"
                                       placeholder="Musterstr. 11">
                            </div>
                        </div>
                        <div class="col-12" id="error-new-street"></div>

                        <div class="col-12">
                            <div class="form-group">
                                <label for="new-postcode">Postcode:</label>
                                <input type="text" class="form-control" name="new-postcode" id="new-postcode"
                                       placeholder="54299">
                            </div>
                        </div>
                        <div class="col-12" id="error-new-postcode"></div>

                        <div class="col-12">
                            <div class="form-group">
                                <label for="new-city">City:</label>
                                <input type="text" class="form-control" name="new-city" id="new-city"
                                       placeholder="Musterhausen">
                            </div>
                        </div>
                        <div class="col-12" id="error-new-city"></div>

                        <div class="col-12">
                            <a href="/people" class="btn btn-warning">Cancel</a>
                            <button id="add-button" type="submit" class="btn btn-primary float-right">Save</button>
                        </div>
                    </div>


                </div>

            </div>
        </div>

    </div>
</div>

