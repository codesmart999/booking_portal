function toggleDisplay() {
  var secondColumn = document.querySelectorAll('td[colspan="1"]');
  for (var i = 0; i < secondColumn.length; i++) {
    secondColumn[i].toggleAttribute("colspan");
  }
}

function showPopupMenu() {
  $("#popupMenu").show(); // Show the popup menu
}

function hidePopupMenu() {
  $("#popupMenu").hide(); // Hide the popup menu
}

function bookedClientView(customer_id) {
  alert(customer_id);
}

function bookedAddComments(booking_id) {
  // Specify the URL you want to open in the small window

  // Define the width and height of the small window
  var width = 800;
  var height = 600;
  // Calculate the left and top position to center the window
  var left = (window.innerWidth - width) / 2;
  var top = (window.innerHeight - height) / 2;
  // Open the small window with specified parameters
  window.open(
    window.location.origin +
      "/admin/options_comments.php?booking_code=" +
      booking_id,
    "_blank",
    "width=" + width + ",height=" + height + ",left=" + left + ",top=" + top
  );
}

function bookedQA(booking_id) {
  alert(booking_id);
}

function bookedViewBookingDetails(booking_id) {
  alert(booking_id);
}
