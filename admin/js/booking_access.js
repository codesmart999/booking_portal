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
