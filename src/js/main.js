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

$("#expresswayId").change(function () {
  $("#tollEntryId").empty();
  $("#tollExitId").empty();
  tollExitFieldSpinnerWrapper.show();
  tollEntryFieldSpinnerWrapper.show();
  loadTollPlaza();
});

const fmtPeso = new Intl.NumberFormat("en-PH", {
  style: "currency",
  currency: "PHP",
  currencyDisplay: "narrowSymbol",
  minimumFractionDigits: 2,
});

let tripTotal = 0;

function addTripCard(entryLabel, exitLabel, feeNumber) {
  const feeText = fmtPeso.format(Number(feeNumber));
  const card = $(`
    <div class="card mb-3 trip-card">
      <div class="card-body d-flex justify-content-between align-items-start">
        <div>
          <label>${entryLabel} <i class="bi bi-arrow-right fs-3 ms-3 me-3"></i> ${exitLabel}</label><br>
          <label>Toll: <span class="fw-bold text-warning">${feeText}</span></label>
        </div>
        <button type="button" class="btn btn-sm btn-outline-danger remove-trip" title="Remove">
          <i class="bi bi-x"></i>
        </button>
      </div>
    </div>
  `).data("fee", Number(feeNumber));

  $("#tripList").append(card);

  tripTotal += Number(feeNumber);
  $("#totalToll").text(`Total Toll: ${fmtPeso.format(tripTotal)}`);
}

$(document).on("click", ".remove-trip", function () {
  const card = $(this).closest(".trip-card");
  const fee = Number(card.data("fee")) || 0;
  tripTotal = Math.max(0, tripTotal - fee);
  $("#totalToll").text(`Total Toll: ${fmtPeso.format(tripTotal)}`);
  card.remove();
});

$("#resetTripsBtn").on("click", function () {
  swal({
    title: "Clear trip list?",
    text: "This will remove all saved trips and reset your total.",
    icon: "warning",
    buttons: true,
    dangerMode: true,
  }).then((willDelete) => {
    if (willDelete) {
      $("#tripList").empty();
      tripTotal = 0;
      $("#totalToll").text(`Total Toll: ${fmtPeso.format(0)}`);

      swal({
        text: "Your trip list has been cleared.",
        icon: "success",
      });
    }
  });
});

$("#fetchTripBtn").on("click", function () {
  $.ajax({
    url: "../src/api/fetchResources.php",
    method: "POST",
    dataType: "json",
    data: {
      method: "getTollFee",
      expressWayLink: $("#expresswayId").val(),
      vehicleClass: $("#vehicleClassId").val(),
      entry: $("#tollEntryId").val(),
      exit: $("#tollExitId").val(),
    },
  })
    .done(function (data) {
      if (data.success) {
        const entryLabel = $("#tollEntryId option:selected").text();
        const exitLabel = $("#tollExitId option:selected").text();
        addTripCard(entryLabel, exitLabel, data.fee);

        swal({
          text: "Added to Trip List",
          icon: "success",
        });
      } else {
        console.warn("Server says not found:", data.message);
        swal({
          text: data.message,
          icon: "info",
        });
      }
    })
    .fail(function (jqXHR) {
      swal({
        text: "Toll Entry/Exit not possible or not exist. Please visit the official Toll Regulatory Board for more information.",
        icon: "info",
      });
      console.error("HTTP error", jqXHR.status, jqXHR.responseText);
    });
});

$(document).ready(function () {
  showHideWrappers();
  loadInitialData();
});

function loadInitialData() {
  var expressWaysRequest = $.post("../src/api/fetchResources.php", {
    method: "expressWays",
  });
  var vehicleClassRequest = $.post("../src/api/fetchResources.php", {
    method: "vehicleClass",
  });

  Promise.all([expressWaysRequest, vehicleClassRequest])
    .then(function (responses) {
      var expressWaysResponse = responses[0];
      var expressWaysData = expressWaysResponse.data;
      var expressWaysLinks = expressWaysResponse.links;

      expressWaysData.forEach(function (item, index) {
        var linkValue = expressWaysLinks[index];

        $("#expresswayId").append(
          $("<option>", {
            value: linkValue,
            text: item,
          })
        );
      });

      expressWayFieldSpinnerWrapper.hide();
      expressWayLink = $("#expresswayId").val();
      loadTollPlaza();

      var vehicleClassResponse = responses[1];
      var vehicleClassData = vehicleClassResponse.data;

      vehicleClassData.forEach(function (item) {
        $("#vehicleClassId").append(
          $("<option>", {
            value: item,
            text: item,
          })
        );
      });

      vehicleClassFieldSpinnerWrapper.hide();
    })
    .catch(function (error) {
      var errorResponse = error.responseText;
      var errorObject = JSON.parse(errorResponse);

      swal({
        title: "Server Error",
        text:
          "The Server responded with a Status " +
          error.status +
          " with message: '" +
          errorObject.message +
          "'",
        icon: "error",
        buttons: {
          confirm: {
            className: "btn btn-danger",
          },
        },
      });
    });
}

function loadTollPlaza() {
  expressWayLink = $("#expresswayId").val();

  $.post(
    "../src/api/fetchResources.php",
    {
      method: "loadTollPlaza",
      expressLink: expressWayLink,
    },
    function (response) {
      var dataArray = response.data;
      var valuesArray = Object.values(dataArray);

      valuesArray.forEach(function (item, index) {
        $("#tollEntryId").append(
          $("<option>", {
            value: item,
            text: item,
          })
        );

        var $option = $("<option>", {
          value: item,
          text: item,
        });

        $("#tollExitId").append($option);

        if (index === 1) {
          $option.prop("selected", true);
        }
      });

      tollExitFieldSpinnerWrapper.hide();
      tollEntryFieldSpinnerWrapper.hide();
    }
  ).fail(function (xhr, status, error) {
    var errorResponse = xhr.responseText;
    var errorObject = JSON.parse(errorResponse);

    swal({
      title: "Server Error",
      text:
        "The Server responded with a Status " +
        xhr.status +
        " with message: '" +
        errorObject.message +
        "'",
      icon: "error",
      buttons: {
        confirm: {
          className: "btn btn-danger",
        },
      },
    });
  });
}

function showHideWrappers() {
  if (enterFieldWrapper.is(":visible")) {
    enterFieldWrapper.hide();
  } else {
    enterFieldWrapper.show();
  }

  if (exitFieldWrapper.is(":visible")) {
    exitFieldWrapper.hide();
  } else {
    exitFieldWrapper.show();
  }
}
