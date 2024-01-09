// Wrappers
const enterFieldWrapper = $("#tollEntryFieldWrapper");
const exitFieldWrapper = $("#tollExitFieldWrapper");
const expressWayFieldSpinnerWrapper = $("#expressWayFieldSpinnerWrapper");
const vehicleClassFieldSpinnerWrapper = $("#vehicleClassFieldSpinnerWrapper");
// Wrappers

// global
let expressWayLink;

$(document).ready(function () {
    showHideWrappers();
    loadInitialData();
});

function loadInitialData() {
    var expressWaysRequest = $.post('../src/api/fetchResources.php', { method: 'expressWays' });
    var vehicleClassRequest = $.post('../src/api/fetchResources.php', { method: 'vehicleClass' });

    Promise.all([expressWaysRequest, vehicleClassRequest])
        .then(function (responses) {
            var expressWaysResponse = responses[0];
            var expressWaysData = expressWaysResponse.data;
            var expressWaysLinks = expressWaysResponse.links;

            expressWaysData.forEach(function (item, index) {
                var linkValue = expressWaysLinks[index];

                $("#expresswayId").append($('<option>', {
                    value: linkValue,
                    text: item
                }));
            });

            expressWayFieldSpinnerWrapper.hide();
            expressWayLink = $("#expresswayId").val();
            loadTollPlaza();

            var vehicleClassResponse = responses[1];
            var vehicleClassData = vehicleClassResponse.data;

            vehicleClassData.forEach(function (item) {
                $("#vehicleClassId").append($('<option>', {
                    value: item,
                    text: item
                }));
            });

            vehicleClassFieldSpinnerWrapper.hide();
        })
        .catch(function (error) {
            var errorResponse = error.responseText;
            var errorObject = JSON.parse(errorResponse);

            swal({
                title: "Server Error",
                text: "The Server responded with a Status " + error.status + " with message: '" + errorObject.message + "'",
                icon: "error",
                buttons: {
                    confirm: {
                        className: 'btn btn-danger',
                    },
                },
            });
        });
}

function loadTollPlaza(){
    expressWayLink = $("#expresswayId").val();

    $.post('../src/api/fetchResources.php', {
        method: 'loadTollPlaza',
        expressLink: expressWayLink
    }, function (response) {
        //var dataArray = response.data;
        console.log(response);

        // dataArray.forEach(function (item) {
        //     $("#tollEntryId").append($('<option>', {
        //         value: item,
        //         text: item
        //     }));

        //     $("#tollExitId").append($('<option>', {
        //         value: item,
        //         text: item
        //     }));
        // });
    }).fail(function (xhr, status, error) {
        var errorResponse = xhr.responseText;
        var errorObject = JSON.parse(errorResponse);

        swal({
            title: "Server Error",
            text: "The Server responded with a Status "+xhr.status+" with message: '"+errorObject.message+"'",
            icon: "error",
            buttons: {
                confirm: {
                    className: 'btn btn-danger',
                },
            },
        });
    });
}

function showHideWrappers() {
    if (enterFieldWrapper.is(':visible')) {
        enterFieldWrapper.hide();
    } else {
        enterFieldWrapper.show();
    }

    if (exitFieldWrapper.is(':visible')) {
        exitFieldWrapper.hide();
    } else {
        exitFieldWrapper.show();
    }
}