<?php 
	require_once('header.php');
?>
<link href="./css/reports.css" rel="stylesheet">
<h4 class="page-title">Report on All Scheduled Bookings</h4>
<p class="form-text fs-6">This feature will allow for a report on Individual Systems, showing Customer information
    supplied with each scheduled booking.</p>
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <form action="/admin/reports_all_customize_result.php" method="get" onsubmit="return validateForm()"
                target="_blank">
                <div class="form-group">

                    <div class="bg-light p-3">
                        <label for="reportCriteria" class="mb-2 fw-bold fs-5 text-danger">Select Report Criteria</label>
                    </div>
                    <div class="row" style="font-size: small">
                        <div class="col-lg-12">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="searchchoice" id="reportOption1"
                                    value="dp">
                                <label class="form-check-label" for="reportOption1">
                                    <b>Report Option 1</b>
                                </label>
                            </div>
                            <div class="col-md-4">
                                <select class="form-select" name="searchbydp1">
                                    <option value="">Select Search Criteria</option>
                                    <?php foreach ($arrLocations as $key => $values): ?>
                                    <option value="<?php echo $key; ?>"><?php echo $values['name']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="searchchoice" id="reportOption2"
                                    value="bs">
                                <label class="form-check-label" for="reportOption2">
                                    <b>Report Option 2</b>
                                </label>
                            </div>
                            <div class="col-md-4 mb-3">
                                <select class="form-select" name="searchbybs">
                                    <option value="">Select Specific Individual System</option>
                                    <?php foreach ($arrServices as $key => $objService): ?>
                                    <option value="<?php echo $key; ?>"><?php echo $objService['fullname']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="searchchoice" id="reportOption3"
                                    value="bsall" checked>
                                <label class="form-check-label" for="reportOption3">
                                    <b>Report Option 3</b>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <p cclass="form-text fs-6" style="font-size: 1rem; margin-left: 20px;">Search ALL Individual
                            Systems
                        </p>
                    </div>
                    <div class="row mb-3">
                        <div class="col">
                            <div class="bg-light p-3">
                                <label for="reportCriteria" class="mb-2 fw-bold fs-5 text-danger">Select Start Date of
                                    Report
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="row" style="font-size: 1rem;">
                        <div class="col-lg-6">
                            <div class="bg-white p-3">
                                <div class="mb-3">
                                    <input type="radio" value="bdate" checked name="searchorder" id="searchorder_bdate">
                                    <label for="searchorder_bdate">Date Booking was made</label>
                                </div>
                                <div class="mb-3">
                                    <input type="radio" value="adate" name="searchorder" id="searchorder_adate">
                                    <label for="searchorder_adate">Scheduled Booking / Appointment Date</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="bg-white p-3">
                                <p class="fs-6">Please select a Date Range. (maximum of 31 days)</p>

                                <label for="startDate" class="form-label">Select Start Date</label>
                                <div class="col-lg-6">
                                    <input id="startDate" name="startDate" type="date" class="form-control" value="">
                                </div>

                                <label for="endDate" class="form-label">Select End Date</label>
                                <div class="col-lg-6">
                                    <input id="endDate" name="endDate" type="date" class="form-control" value="">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- <div class="row">
                        <div class="col">
                            <div class="bg-light p-3">
                                <label for="reportCriteria" class="mb-2 fw-bold fs-5 text-danger">Select scheduled
                                    booking
                                    details you wish to display in your report</label>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="bg-white p-3">
                                <div class="mb-3">
                                    <div class="form-check" id="display_check">
                                        <input class="form-check-input" type="checkbox" name="printuser" id="printuser"
                                            checked>
                                        <label class="form-check-label" for="printuser"
                                            style="font-size: 1rem; margin-top: 5px;">User</label><br>

                                        <input class="form-check-input" type="checkbox" name="printtitle"
                                            id="printtitle" checked>
                                        <label class="form-check-label" for="printtitle"
                                            style="font-size: 1rem; margin-top: 5px;">Title Label</label><br>

                                        <input class="form-check-input" type="checkbox" name="printname1"
                                            id="printname1" checked>
                                        <label class="form-check-label" for="printname1"
                                            style="font-size: 1rem; margin-top: 5px;">Name Label 1 (Default : First
                                            Name)</label><br>

                                        <input class="form-check-input" type="checkbox" name="printname2"
                                            id="printname2" checked>
                                        <label class="form-check-label" for="printname2"
                                            style="font-size: 1rem; margin-top: 5px;">Name Label 2 (Default : Last
                                            Name)</label><br>

                                        <input class="form-check-input" type="checkbox" name="printlink" id="printlink"
                                            checked>
                                        <label class="form-check-label" for="printlink"
                                            style="font-size: 1rem; margin-top: 5px;">System Name</label><br>

                                        <input class="form-check-input" type="checkbox" name="printdate" id="printdate"
                                            checked>
                                        <label class="form-check-label" for="printdate"
                                            style="font-size: 1rem; margin-top: 5px;">Date of Booking</label><br>

                                        <input class="form-check-input" type="checkbox" name="printstatus"
                                            id="printstatus" checked>
                                        <label class="form-check-label" for="printstatus"
                                            style="font-size: 1rem; margin-top: 5px;">Status</label><br>

                                    </div>
                                </div>
                                <div class="mb-3">
                                    <button class="report_btn" style="padding: 5px 10px;" onclick="checkAll()">Check
                                        All</button>&nbsp;&nbsp;&nbsp;
                                    <button class="report_btn" style="padding: 5px 10px;" onclick="uncheckAll()">UnCheck
                                        All</button>

                                </div>
                            </div>
                        </div> -->

                        <div class="row">
                            <div class="col">
                                <div class="bg-light p-3">
                                    <label for="displayFormat" class="mb-2 fw-bold fs-5 text-danger">Select Display
                                        Format</label>
                                </div>
                            </div>
                        </div>
                        <div class="row" style="font-size: 1rem;">
                            <div class="col-lg-12">
                                <div class="bg-white p-3">
                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="output"
                                                id="output_screen" value="screen" checked>
                                            <label class="form-check-label" for="output_screen">Write report to
                                                screen</label>
                                        </div>
                                        <p class="mt-2">Selected Report displays to screen</p>
                                    </div>
                                    <div class="mb-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="output" id="output_csv"
                                                value="csv">
                                            <label class="form-check-label" for="output_csv">Export to CSV</label>
                                        </div>
                                        <p class="mt-2">Selected Report can be saved to your computer in CSV (Comma
                                            Separated
                                            Value) format. It can then be viewed with spreadsheet software. The data can
                                            also be
                                            imported into a database.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-2">
                                <button class="report_btn" type="submit" style="padding: 5px 10px;">Generate
                                    Report</button>
                            </div>
                            <div class="col-2">
                                <button class="report_btn" type="button" onclick="history.go(-1);"
                                    style="padding: 5px 10px;">Previous Page</button>
                            </div>
                        </div>
                    </div>

                </div>
            </form>
        </div>
    </div>
</div>

<script>
function checkAll() {
    alert("Displaying Messages and Comments will increase the time it takes for the report to download.");
    const checkboxes = document.querySelectorAll('#display_check input[type="checkbox"]');
    checkboxes.forEach(checkbox => checkbox.checked = true);
    event.preventDefault();
}

function uncheckAll() {
    const checkboxes = document.querySelectorAll('#display_check input[type="checkbox"]');
    checkboxes.forEach(checkbox => checkbox.checked = false);
    event.preventDefault();
}


function validateForm() {
    var startDate = document.getElementById("startDate").value;
    var endDate = document.getElementById("endDate").value;

    // Check if start date is selected
    if (startDate === "") {
        alert("Please select a Calendar Start Date");
        return false;
    }

    // Check if end date is selected
    if (endDate === "") {
        alert("Please select a Calendar End Date");
        return false;
    }

    // Convert start and end dates to Date objects
    var startDateObj = new Date(startDate);
    var endDateObj = new Date(endDate);

    // Calculate the difference in days between start and end dates
    var differenceInTime = endDateObj.getTime() - startDateObj.getTime();
    var differenceInDays = differenceInTime / (1000 * 3600 * 24);

    // Check if the period is over 31 days
    if (differenceInDays > 31) {
        alert("Search must be within a 31 day period");
        return false;
    }
    var reportOption = document.querySelector('input[name="searchchoice"]:checked').value;


    if (reportOption === "dp") {
        // Validate the search criteria dropdown
        var searchCriteria = document.getElementsByName("searchbydp1")[0].value;

        // Check if the default option is chosen
        if (searchCriteria === "") {
            alert("Search Criteria has not been selected");
            return false; // Prevent form submission
        }
    }
    if (reportOption === "bs") {
        // Validate the search criteria dropdown
        var searchCriteria = document.getElementsByName("searchbybs")[0].value;

        // Check if the default option is chosen
        if (searchCriteria === "") {
            alert("System has not been selected");
            return false; // Prevent form submission
        }
    }
    // If all validations pass, return true to allow form submission
    return true;
}
</script>