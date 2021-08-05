<?php
$session = \Config\Services::session();
?>

<div class="container">
    <h1 class="mt-3 text-light">People</h1>
    <div class="row mt-3">


        <div class="col-lg-12">
            <div class="card">
                <div class="card-header row">
                    <a type="button" class="col-lg-2 btn btn-primary" href="/addPerson">
                        Add Person
                    </a>
                    <?php
                    if ($session->getFlashdata('success')):
                        ?>
                        <div class="col-lg-1"></div>
                        <div class="bg-success text-light col-lg-9 d-flex justify-content-center align-items-center">
                            <?= $session->getFlashdata("success") ?>
                        </div>
                    <?php
                    endif;
                    ?>
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
                window.location.href = "<?= base_url(); ?>/people/deletePerson/" + id;
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
</script>

