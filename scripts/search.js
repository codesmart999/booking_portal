function lookup(inputString) {
    if (inputString.length == 0) {
        // Hide the suggestion box.
        $('#suggestions').slideUp();
    } else {
        $.post("api/rpc.php", { queryString: "" + inputString + "" }, function (data) {
            if (data.length > 0) {
                $('#suggestions').slideDown();
                $('#autoSuggestionsList').html(data);
            }
        });
    }
}

// get User profile by ID
function getProfile(profileId) {
    if (profileId) {
        $.post("api/rpcProfile.php", { queryString: "" + profileId + "" }, function (data) {
            if (data.length > 0) {
                profile = JSON.parse(data);

                $("#profile_id").val( profile.id );
                $("#business_name").val( profile.business_name );
                $("#street").val( profile.street );
                $("#city").val( profile.city );
                $("#state").val( profile.state );
                $("#postcode").val( profile.postcode );
                $("#email_addr").val( profile.email_addr );
                $("#phone_number").val( profile.phone_number );

                // replace 
                $("td.profile_label").html("View/Edit Profile");

                $('#suggestions').slideUp();
            }
        });
    }
}

function doSearch() { $('#SearchForm').submit(); }

function searchAllUsers() {
    $('#inputString').val("");
    doSearch();
    return false;
}

function showProfile(id) {
    window.location.href = s_url + "?id=" + id;
}
