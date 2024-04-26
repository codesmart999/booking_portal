<?php
    define('SIDEBAR_CLASS', 'active'); // hide the sidebar

	require_once('header.php');
	require_once('utils.php');

	$message = "";
    $db = getDBConnection();

    $selected_date = isset($_GET['selected_date']) ? $_GET['selected_date'] : date("d/m/Y");

    $end_date = date('d/m/Y', strtotime(str_replace('/', '-', $selected_date) . ' +31 days'));

    $arrLocations = array();
    $stmt = $db->prepare("SELECT LocationId, LocationName FROM locations where deleted = 0");
	$stmt->execute();
    $stmt->bind_result($location_id, $location_name);
    $stmt->store_result();
    while ($stmt->fetch()) {
    	$arrLocations[$location_id] = array(
            'location_name' => $location_name,
            'selected' => isset($_GET['location']) && $_GET['location'] == $location_id ? 'selected' : ''
        );
    }

    $arrSystems = array();

    $query = 'SELECT SystemId, FullName FROM systems';
    if (!empty($_GET['location']))
        $query .= ' WHERE LocationId = ' . $_GET['location'];

    $stmt = $db->prepare($query);
	$stmt->execute();
    $stmt->bind_result($system_id, $full_name);
    $stmt->store_result();
    while ($stmt->fetch()) {
    	$arrSystems[$system_id] = array(
            'full_name' => $full_name,
            'selected' => isset($_GET['system']) && $_GET['system'] == $system_id ? 'selected' : ''
        );
    }

    $arrSystemIds = !empty($_GET['system']) ? array($_GET['system']) : array_keys($arrSystems);
    
    $stmt = $db->prepare("SELECT LocationId, LocationName FROM locations where deleted = 0");
	$stmt->execute();
    $stmt->bind_result($location_id, $location_name);
    $stmt->store_result();
    while ($stmt->fetch()) {
    	$arrLocations[$location_id] = array(
            'location_name' => $location_name,
            'selected' => isset($_GET['location']) && $_GET['location'] == $location_id ? 'selected' : ''
        );
    }

    $arrMonthlySummaryBySystems = getMonthlySummary($arrSystemIds, date("Y-m-d", strtotime(str_replace('/', '-', $selected_date))));
?>

<h4 class="page-title">Monthly Summary (<?php echo format_date($selected_date) . ' ~ ' . format_date($end_date); ?>)</h4>

<div class="container-fluid move-booking-container">
    <form method="get" class="form-horizontal" id="FRM_MONTHLY_SUMMARY">
        <div class="row">
            <div class="col-md-4 panel-container">
                <div class="row">
                    <div class="col-md-12 panel-header">
                        Select a date range.
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <label for="selected_date" class="form-control form-control-sm">Start From:</label>
                    </div>
                    <div class="col-md-8 input-group">
                        <div class="input-group-append">
                            <span class="input-group-text"><i class="fa fa-calendar"></i></span> <!-- Date icon -->
                        </div>
                        <input type="text" id="selected_date" name="selected_date" class="date valid form-control form-control-sm" placeholder="dd/mm/yyyy" value="<?php echo $selected_date; ?>"/>
                    </div>
                </div>
            </div>
            <div class="col-md-4 panel-container">
                <div class="row">
                    <div class="col-md-12 panel-header">
                        Select a location.
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12 input-group">
                        <select class="custom-select" id="location" name="location">
                            <option value="0">---Show All---</option>
                        <?php
                            foreach ($arrLocations as $location_id => $objLocation) {
                                echo "<option value='$location_id' " . $objLocation['selected'] . '>' . $objLocation['location_name'] . '</option>';
                            }
                        ?>
                        </select>
                    </div>
                </div>
            </div>
            <div class="col-md-4 panel-container">
                <div class="row">
                    <div class="col-md-12 panel-header">
                        Select an Individual System.
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12 input-group">
                        <select class="custom-select" id="system" name="system">
                            <option value="0">---Show All---</option>
                        <?php
                            foreach ($arrSystems as $system_id => $objSystem) {
                                echo "<option value='$system_id' " . $objSystem['selected'] . '>' . $objSystem['full_name'] . '</option>';
                            }
                        ?>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<div class="table-responsive">
    <table border="0" cellspacing="0" cellpadding="5" width="100%" class="table">
        <thead>
            <tr>
                <td nowrap><b>Individual System</b></td>
                <?php
                    for ($i = 0; $i <= 30; $i++) {
                        $current_timestamp = strtotime(str_replace('/', '-', $selected_date) . ' +' . $i . ' days');
                        $current_date = date('d', $current_timestamp);
                        $current_weekday = date('D', $current_timestamp);

                        echo '<td>' . substr($current_weekday, 0, 2) . '<br/>' . $current_date . '</td>';
                    }
                ?>
            </tr>
        </thead>
        <tbody>
        <?php
            foreach ($arrMonthlySummaryBySystems as $system_id => $arrMonthlySummary) {
                if (!empty($_GET['system']) && $system_id != $_GET['system'])
                    continue;

                echo '<tr>';
                echo '<td><a href="#" class="editSystem">' . $arrSystems[$system_id]['full_name'] . '</a></td>';

                for ($i = 0; $i <= 30; $i++) {
                    $current_timestamp = strtotime(str_replace('/', '-', $selected_date) . ' +' . $i . ' days');
                    $current_date = date('Y-m-d', $current_timestamp);
                    
                    if (!isset($arrMonthlySummary[$i])) {
                        echo '<td></td>';
                    } else {
                        echo '<td>' . $arrMonthlySummary[$i]['single_bookings'] . '<br/>'
                            . '<a href="booking_access?SystemId=' . $system_id . '&startDate=' . $current_date . '&endDate=' . $current_date . '">' . $arrMonthlySummary[$i]['available_slots']
                            . '</a></td>';
                    }
                }

                echo '</tr>';
            }
        ?>
        </tbody>
    </table>
</div>

<?php
    $stmt->close();
    $db->close();

    require_once('footer.php');
?>

<script>
	$(document).ready(function() { 
        $("#FRM_MONTHLY_SUMMARY select").change(function() {
            $("#FRM_MONTHLY_SUMMARY").submit();
        });

        $("#selected_date").on("dateSelected", function() {
            $("#FRM_MONTHLY_SUMMARY").submit();
        })
    });
</script>