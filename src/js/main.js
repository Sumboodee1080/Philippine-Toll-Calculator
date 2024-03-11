// Wrappers
const enterFieldWrapper = $("#tollEntryFieldWrapper");
const exitFieldWrapper = $("#tollExitFieldWrapper");
const expressWayFieldSpinnerWrapper = $("#expressWayFieldSpinnerWrapper");
const vehicleClassFieldSpinnerWrapper = $("#vehicleClassFieldSpinnerWrapper");
const tollEntryFieldSpinnerWrapper = $("#tollEntryFieldSpinnerWrapper");
const tollExitFieldSpinnerWrapper = $("#tollExitFieldSpinnerWrapper");
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

$("#expresswayId").change(function() {
    $("#tollEntryId").empty();
    $("#tollExitId").empty();
    tollExitFieldSpinnerWrapper.show();
    tollEntryFieldSpinnerWrapper.show();
    loadTollPlaza();
});

function loadTollPlaza() {
    expressWayLink = $("#expresswayId").val();

    $.post('../src/api/fetchResources.php', {
        method: 'loadTollPlaza',
        expressLink: expressWayLink
    }, function (response) {
        var dataArray = response.data;
        var valuesArray = Object.values(dataArray);

        valuesArray.forEach(function (item, index) {
            $("#tollEntryId").append($('<option>', {
                value: item,
                text: item
            }));

            var $option = $('<option>', {
                value: item,
                text: item
            });

            $("#tollExitId").append($option);

            if (index === 1) {
                $option.prop('selected', true);
            }
        });

        tollExitFieldSpinnerWrapper.hide();
        tollEntryFieldSpinnerWrapper.hide();
    }).fail(function (xhr, status, error) {
        var errorResponse = xhr.responseText;
        var errorObject = JSON.parse(errorResponse);

        swal({
            title: "Server Error",
            text: "The Server responded with a Status " + xhr.status + " with message: '" + errorObject.message + "'",
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