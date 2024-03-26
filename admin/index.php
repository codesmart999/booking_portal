<?php 
require_once('header.php');

$options = "";
$db = getDBConnection();
$query = "SELECT SystemId, FullName FROM systems";
$result = $db->query($query);
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $options .= '<option value="' . $row["SystemId"] . '">' . $row["FullName"] . '</option>';
    }
}
?>



<div class="container-fluid">
    <div class="row">
        <div class="col-md-12" bgcolor="#FFFFFF" valign="top" align="left">

            <div class="card">
                <div class="card-header" bgcolor="#E8EEF7">
                    Access an Individual System
                </div>
                <div class="card-body" bgcolor="#FFFFFF">
                    <select class="custom-select" name="gourl" size="10">
                        <?php echo $options; ?>
                    </select>
                    <br>
                    <button class="btn btn-secondary mt-3" onclick="redirectToSelectedUrl()">Access System</button>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
    function redirectToSelectedUrl() {
        // Get the selected option
        var selectBox = document.querySelector('.custom-select');
        var selectedOption = selectBox.options[selectBox.selectedIndex];

        if (selectedOption) {
            // Get the SystemId from the value attribute
            var systemId = selectedOption.value;
            // Redirect to the booking_access.php with SystemId parameter
            window.location.href = '/admin/booking_access.php?SystemId=' + systemId;
        } else {
            alert("Please select an option.");
        }
    }
</script>

<style>
    .custom-select {
        width: 30%;
        color: black;
    }

    .custom-select option:checked {
        background-color: lightgrey;
    }
</style>


<?php
require_once('footer.php');
?>
