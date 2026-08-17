$(function () {
  $(".table-btn").on("click", function () {
    const url = $(this).attr("data-url");
    const onid = $(this).attr("data-attendee-onid");
    const slotHash = $(this).attr("data-timeslot-hash");

    $("#delete-attendee-modal").modal("show");

    $("#delete-attendee-button").click(function () {
      removeAttendee(slotHash, onid, url);
    });
  });
});

function removeAttendee(slotHash, onid, url) {
  $.ajax({
    url: url,
    type: "POST",
    data: {
      slotHash: slotHash,
      attendeeOnid: onid,
    },
  }).done(function (response) {
    $("#delete-attendee-modal").modal("hide");
    location.reload();
  });
}
