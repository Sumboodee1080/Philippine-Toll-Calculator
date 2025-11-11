<?php echo ini_get('error_log'); ?>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="src/css/index.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.3.0/font/bootstrap-icons.css">

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    <script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>

    <title>Philippines Toll Calculator</title>
</head>

<body>
    <!-- NAV ITEMS -->
    <nav class="navbar navbar-expand-lg fixed-top navbar-dark bg-dark">
        <!-- <nav class="navbar navbar-expand-lg navbar-dark bg-dark"> -->
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <img src="src/img/Philippine Toll Calculator.png" alt="" width="50" height="50" class="d-inline-block">
                <label style="color: #2B75FF;">P</label><label style="color: #CE1227;">H</label> Toll Calculator
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavAltMarkup" aria-controls="navbarNavAltMarkup" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-end" id="navbarNavAltMarkup">
                <div class="navbar-nav">
                    <a class="nav-link" aria-current="page" href="#trip-calculator">Trip Calculator</a>
                    <a class="nav-link" href="#about">About</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Calculator -->
    <div class="container" style="margin-top: 13vh;" id="trip-calculator">
        <div class="row">
            <div class="col-md-6">
                <div class="h-100">
                    <h5>Select your Destination</h5>
                    <div class="mt-4 mb-2">
                        <label for="expresswayId">Which Expressway are you passing through?</label>
                        <div class="spinner-border spinner-border-sm ms-2" role="status" id="expressWayFieldSpinnerWrapper">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <select class="form-select mt-2" id="expresswayId" required></select>
                    </div>
                    <div class="mb-2">
                        <label for="vehicleClassId">Select your Vehicle Class <a href="https://trb.gov.ph/index.php/faqs/vehicle-classification" target="_blank"><i class="bi bi-info-circle"></i></a></label>
                        <div class="spinner-border spinner-border-sm ms-2" role="status" id="vehicleClassFieldSpinnerWrapper">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <select class="form-select mt-2" id="vehicleClassId" required></select>
                    </div>
                    <div class="mb-2" id="tollEntryFieldWrapper" style="display: none;">
                        <label for="tollEntryId">Where are you entering?</label>
                        <div class="spinner-border spinner-border-sm ms-2" role="status" id="tollEntryFieldSpinnerWrapper">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <select class="form-select mt-2" id="tollEntryId" required>
                        </select>
                    </div>
                    <div class="mb-2" id="tollExitFieldWrapper" style="display: none;">
                        <label for="tollExitId">Where are you exiting?</label>
                        <div class="spinner-border spinner-border-sm ms-2" role="status" id="tollExitFieldSpinnerWrapper">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <select class="form-select mt-2" id="tollExitId" required>
                        </select>
                    </div>
                    <div class="alert alert-primary d-flex align-items-center" role="alert">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-exclamation-triangle-fill flex-shrink-0 me-2" viewBox="0 0 16 16" role="img" aria-label="Warning:">
                            <path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16zm.93-9.412-1 4.705c-.07.34.029.533.304.533.194 0 .487-.07.686-.246l-.088.416c-.287.346-.92.598-1.465.598-.703 0-1.002-.422-.808-1.319l.738-3.468c.064-.293.006-.399-.287-.47l-.451-.081.082-.381 2.29-.287zM8 5.5a1 1 0 1 1 0-2 1 1 0 0 1 0 2z" />
                        </svg>
                        <div>
                            Add as much trips as you want to calculate the total tolls.
                        </div>
                    </div>
                    <button id="fetchTripBtn" type="button" class="btn btn-dark mt-3">Add to Trip List<i class="bi bi-calculator ms-2"></i></button>
                </div>
            </div>
            <div class="col-md-6">
                <div class="h-100 p-3">
                    <p class="fs-6 fw-light"><strong>Disclaimer: </strong>Data shown here is derived from the Official Website of <a href="https://trb.gov.ph/index.php" target="_blank">Toll Regulatory Board</a>. The accuracy of data being displayed here depends on the data from the website.</p>

                    <div class="card-body d-flex justify-content-between align-items-center">
                        <label class="fw-bolder mt-1">Trip List</label>
                        <button id="resetTripsBtn" type="button" class="btn btn-outline-secondary">Reset <i class="bi bi-arrow-clockwise"></i></button>
                    </div>
                    <div id="tripList"></div>
                    <h5 id="totalToll">Total Toll: ₱0.00</h5>
                </div>
            </div>
        </div>
    </div>

    <!-- About -->
    <div class="container" style="margin-top: 5vh;" id="about">
        <div class="row">
            <div class="h-100">
                <h3>About</h3>
                <p>This website is a personal project created to kill time and together consider the needs of having an application that can conveniently calculate the toll fees.<br>Google has a Toll Fee Total Counting feature but is limited to selected regions. This Application helps you have the same functionality. If the Data being shown here is inaccurate, blame the Government Website as the data used here is derived to what is in there xdd.<br>
                    This project is created for free. If you find this website useful, please turn off your adblocker and let me display ads. This way I can earn money from this xoxo</p>
            </div>
        </div>
    </div>

    <script src="src/js/main.js"></script>
</body>

</html>