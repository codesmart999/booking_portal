function lookup(inputString) {
  if (inputString.length == 0) {
    // Hide the suggestion box.
    $("#suggestions").slideUp();
  } else {
    $.post(
      "api/rpc.php",
      { queryString: "" + inputString + "" },
      function (data) {
        if (data.length > 0) {
          $("#autoSuggestionsList").html("");
          var results = JSON.parse(data);
          var htmlData = "<ul>";
          $.each(results, function (index, result) {
            htmlData +=
              `<li onclick="getProfile('` +
              result.id +
              `');">` +
              result.name +
              `
            </li>`;
          });

          $("#suggestions").slideDown();
          $("#autoSuggestionsList").html(htmlData);
        }
      }
    );
  }
}

// get User profile by ID
function getProfile(profileId) {
  console.log(profileId);
  if (profileId) {
    $.post(
      "api/rpcProfile.php",
      { queryString: "" + profileId + "" },
      function (data) {
        if (data.length > 0) {
          profile = JSON.parse(data);
          var addrJsonString = profile.addr;
          var addrObject = JSON.parse(addrJsonString);
          console.log(addrObject);
          $("#profile_id").val(profile.id);
          $("#business_name").val(profile.business_name);
          $("#street").val(addrObject.street);
          $("#city").val(addrObject.city);
          $("#state").val(addrObject.state);
          $("#postcode").val(addrObject.postcode);
          $("#email_addr").val(profile.email_addr);
          $("#phone_number").val(profile.phone_number);

          // replace
          $("td.profile_label").html("View/Edit Profile");

          $("#suggestions").slideUp();
        }
      }
    );
  }
}

function doSearch() {
  $("#SearchForm").submit();
}

function searchAllUsers() {
  $("#inputString").val("");
  doSearch();
  return false;
}

function showProfile(id) {
  window.location.href = s_url + "?id=" + id;
}
