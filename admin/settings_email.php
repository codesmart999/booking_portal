<?php 
	require_once('header.php');

	$message = "";
    $db = getDBConnection();
    // Save Settings
    if( isset($_POST['Save']) ) {
   	    $emailAddress = $_POST['userreplyaddress'].'@gobookings.com';
        $key = "EMAIL_ADDRESS";

        $stmt = $db->prepare('SELECT COUNT(*) FROM settings WHERE name = ?');
        $stmt->bind_param('s', $key);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();
        
        if ($count > 0) {
            // If the setting exists, update its value
            $stmt = $db->prepare('UPDATE settings SET value = ? WHERE name = ?');
            $stmt->bind_param('ss', $emailAddress, $key);
        } else {
            // If the setting does not exist, insert a new record
            $stmt = $db->prepare('INSERT INTO settings (name, value, category) VALUES (?, ?, "email")');
            $stmt->bind_param('ss', $key, $emailAddress);
        }

        
        $stmt->execute();
        $stmt->close();

		$message = '<p class="Message fst-italic fw-bold text-success show">'._lang('success_update').'</p>';
		unset($_POST['Save']);
    }

    if( isset($_POST['Send']) ) {
        $message = '<p class="Message fst-italic fw-bold text-success show">'._lang('success_email_send').'</p>';
		unset($_POST['Save']);
    }

    
    $stmt = $db->prepare("SELECT value FROM settings WHERE name = 'EMAIL_ADDRESS' LIMIT 1");

	$stmt->execute();
    $stmt->bind_result($emailAddress);
    $stmt->fetch();
    $stmt->close();

    if (!$emailAddress) {
        // If no address is found, set $address to 'noreply'
        $emailAddress = 'noreply';
    }else {
        $emailAddress = substr($emailAddress, 0, strpos($emailAddress, "@"));
    }
?>
<h4 class="page-title">Email Settings</h4>
<?php echo $message ?>
<div class="table-responsive">
    <table border="0" cellspacing="0" cellpadding="5" width="100%" class="table">
        <thead>
            <tr>
                <td width="10" nowrap>Name</td>
                <td width="90" nowrap>Description</td>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><a href="#" data-toggle="modal" data-target="#statusModal">Email All</a></td>
                <td>Email all Individual System Managers.
                    <div class="modal fade" id="statusModal" tabindex="-1" role="dialog"
                        aria-labelledby="saveModalLabel" aria-hidden="true">
                        <form method="post" class="form-horizontal setting_form" id="">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="saveModalLabel">
                                            Email All Individual System Managers</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="container">
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <div class="form-group">
                                                        <label for="subject" class="font-weight-bold">Subject</label>
                                                        <input type="text" class="form-control" id="subject" name="T3"
                                                            maxlength="50">
                                                        <small class="form-text text-muted">Maximum Characters:
                                                            50</small>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <div class="form-group">
                                                        <label for="message" class="font-weight-bold">Message</label>
                                                        <textarea class="form-control" id="message" name="emailmessage"
                                                            rows="12" maxlength="1000"></textarea>
                                                        <small class="form-text text-muted">Maximum Characters:
                                                            1000</small>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>


                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary btn-sm"
                                            data-dismiss="modal">Close</button>
                                        <button type="submit" class="btn btn-primary btn-sm" name="Send" value="Send"
                                            id="btnSave">Send Email</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </td>
            </tr>
            <tr>
                <td><a href="#" data-toggle="modal" data-target="#attendanceModal">Email Reply-to Address</a></td>
                <td>Select if booking/reminder Emails use the address DoNotReply@GObookings.com
                    <div class="modal fade" id="attendanceModal" tabindex="-1" role="dialog"
                        aria-labelledby="saveModalLabel" aria-hidden="true">
                        <form method="post" class="form-horizontal setting_form" id="">
                            <div class="modal-dialog" role="document">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="saveModalLabel">
                                            Email Reply-to Address</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="container">
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <div class="bg-white p-4">
                                                        <p class="mb-0">
                                                            Reply Address is the Individual System Email Address
                                                        </p>
                                                        <div class="input-group mb-3">
                                                            <input type="text" class="form-control"
                                                                placeholder="From address" aria-label="From address"
                                                                aria-describedby="basic-addon2" name="userreplyaddress"
                                                                value="<?php echo $emailAddress;?>" style="border: 1px solid #ced4da;">
                                                            <div class="input-group-append">
                                                                <span class="input-group-text"
                                                                    id="basic-addon2">@gobookings.com</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary btn-sm"
                                            data-dismiss="modal">Close</button>
                                        <button type="submit" class="btn btn-primary btn-sm" name="Save" value="Save"
                                            id="btnSave">Save</button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>
</div>
<?php
require_once('footer.php');
?>