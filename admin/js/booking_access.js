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

function bookedClientView(customer_id, booking_id) {
  // Define the width and height of the small window
  var width = 800;
  var height = 600;
  // Calculate the left and top position to center the window
  var left = (window.innerWidth - width) / 2;
  var top = (window.innerHeight - height) / 2;
  // Open the small window with specified parameters
  var newWindow = window.open(
    window.location.origin +
      "/admin/options_clients_view.php?customer_id=" + customer_id + "&booking_id=" + booking_id,
    "_blank",
    "width=" + width + ",height=" + height + ",left=" + left + ",top=" + top
  );

  // Check if the new window is closed every 500 milliseconds
  var checkWindowClosed = setInterval(function() {
    if (newWindow.closed) {
      clearInterval(checkWindowClosed); // Stop checking when window is closed
      window.location.reload(); // Reload the current page
    }
  }, 500);
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

function bookedViewBookingDetails(booking_code) {
  var width = 800;
  var height = 600;
  // Calculate the left and top position to center the window
  var left = (window.innerWidth - width) / 2;
  var top = (window.innerHeight - height) / 2;
  // Open the small window with specified parameters
  window.open(
    window.location.origin +
      "/admin/options_viewprint_bookings.php?booking_code=" +
      booking_code,
    "_blank",
    "width=" + width + ",height=" + height + ",left=" + left + ",top=" + top
  );
}
