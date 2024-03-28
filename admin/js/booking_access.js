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
  alert(booking_id);
}

function bookedQA(booking_id) {
  alert(booking_id);
}

function bookedViewBookingDetails(booking_id) {
  alert(booking_id);
}
