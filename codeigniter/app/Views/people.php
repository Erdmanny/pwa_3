<div class="container">
    <h1 class="mt-3 text-light">People</h1>
    <div class="row mt-3">


        <div class="col-lg-12">
            <div class="card">
                <div class="card-header row">
                    <a type="button" class="col-lg-2 btn btn-primary" href="http://localhost/people/addPerson">
                        Add Person
                    </a>

                    <div class="col-1"></div>
                    <div id="feedback" class="col-10 text-light col-lg-9 d-flex justify-content-center align-items-center"></div>

                </div>
                <div class="card-body">
                    <table
                            id="peopleTable"
                            data-toggle="table"
                            data-mobile-responsive="true"
                            data-pagination="true">

                    </table>
                </div>
            </div>

        </div>

    </div>
</div>

<script>
    function deletePerson(id) {
        if (navigator.onLine) {
            if (confirm("Are you sure you want to remove the person with id " + id + " ?")) {
                window.location.href = "http://localhost/people/deletePerson/" + id;
            }
        } else {
            alert("You cannot delete a person while offline.")
        }
    }


    if (!navigator.onLine){
        if (document.getElementById("delete-button") !== null) {
            document.getElementById("delete-button").disabled = true;
        }
    }


    for(let i = 0; i <ca.length; i++) {
        let c = ca[i];
        while (c.charAt(0) === ' ') {
            c = c.substring(1);
        }
        if (c.indexOf("error") === 0) {
            let text = c.split("=")[1];
            document.getElementById("feedback").classList.add("bg-error");
            document.getElementById("feedback").innerText = text;
         }
        if (c.indexOf("success") === 0) {
            let text = c.split("=")[1];
            document.getElementById("feedback").classList.add("bg-success");
            document.getElementById("feedback").innerText = text;
        }
    }
</script>

