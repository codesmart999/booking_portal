<?php 
    require_once('../config.php');
    require_once('../lib.php');

    $booking_code = -1;

    if ($_SERVER["REQUEST_METHOD"] == "POST") {

        $user = $_SESSION['User'];
        if ($user['UserId'] == 0){ //excption handling
	    	header('Location: '. SECURE_URL . LOGIN_PAGE, true, 301);
		   	exit(0);
	    }
        // Retrieve the content of the textarea
        
        $booking_code = $_POST["bookingCode"];
        $comment_id = $_POST["commentId"];
        $flag = $_POST["Type"];
        if ($flag == "Yes"){
            deletBookingCommentsWithCommentId($booking_code, $comment_id);
        }
        header('Location: '. SECURE_URL . "/admin/options_comments.php?booking_code=".$booking_code, true, 301);

        

        // Display the submitted content
    }

    //get User id and full name from users DB
    $userInfo = getUserInfo();

    if ($booking_code == -1) {
        if (isset($_GET['booking_code'])) {
            // Extract the value of startDate
            $booking_code = $_GET['booking_code'];
            $comment_id = $_GET['comment_id'];
        }else {
            header('Location: '. SECURE_URL . ADMIN_INDEX, true, 301);
            exit; // Make sure to exit after redirection to prevent further script execution
        }
    }
    

    $booking_info = getBookedInfoByBookingCode($booking_code);
    

    if (!isset($booking_info["businessName"]) || !isset($booking_info["bookingCode"]) || !isset($booking_info["bookingDate"]) || !isset($booking_info["startTime"]) || !isset($booking_info["endTime"])) {//exception handling
        header('Location: '. SECURE_URL . ADMIN_INDEX, true, 301);
        exit; // Make sure to exit after redirection to prevent further script execution
    }
    $bookingDate = date('l, F jS, Y', strtotime($booking_info["bookingDate"]));

    $newStartTime = date('g:i A', strtotime("today +{$booking_info['startTime']} minutes"));
    $newEndTime = date('g:i A', strtotime("today +{$booking_info['endTime']} minutes"));

    $comments_array = json_decode($booking_info["comments"], true);
    $content = '';
    $dateTime = '';

    foreach ($comments_array as $comment) {
        if ($comment["id"] == $comment_id){
            $dateTime = $comment["datetime"];
            $content = $comment["content"];
        }
    }

    $bookingTime = "$newStartTime - $newEndTime";

    $commnetsShow = false;
    if (is_array($comments_array) && count($comments_array) > 0) {
        $commnetsShow = true;
    }
?>
<form name="form1" method="POST" action="options_comments_delete.php" onsubmit="return validate()">
    <input type="hidden" name="bookingCode" value="<?php echo $booking_code;?>">
    <input type="hidden" name="commentId" value="<?php echo $comment_id;?>">

    <table border="0" cellpadding="3" width="100%" cellspacing="1" bgcolor="navy">
        
        <tbody>
            <tr>
                <td width="100%" bgcolor="#C5D4F0" align="center" valign="middle"><font face="Arial" size="2" color="#000000"><b>
                    Delete Comments
                </b></font></td>
            </tr>
            <tr>
                <td width="100%" bgcolor="#FFFFFF" valign="top" align="left">
                    <table border="0" cellpadding="6" width="100%" cellspacing="1" bgcolor="gray" bordercolor="gray">
                        <tbody>
                            <tr>
                                <td width="100%" bgcolor="#F9F9F9" align="left" valign="top">
                                    <font face="Arial" size="2" color="#000000">
                                        Comment to be Deleted
                                    </font>
                                </td>
                            </tr>
                            <tr>
                                <td width="100%" bgcolor="#FFFFFF" align="left" valign="top">
                                    <font face="Arial" size="2" color="#000000"><br>
                                    <?php 
                                        $converted_date_time = date('D j M Y g:i A', strtotime($dateTime));
                                        echo $converted_date_time;?>
                                        <br>
                                        <?php echo $content;?>
                                        <br><br>
                                    </font>
                                </td>
                            </tr>
                            <tr>
                                <td width="100%" bgcolor="#FFFFFF" align="left" valign="top">
                                    <font face="Arial" size="2" color="#000000"><br>
                                        Delete above Comment?
                                        <br><br>
                                        <input type="submit" name="Type" value="Yes">&nbsp;&nbsp;
                                        <input type="submit" name="Type" value="No">&nbsp;&nbsp;

                                        <input type="button" value="Close to Refresh Calendar" onclick="javascript:self.close();window.opener.location.reload();">

                                        <br><br>
                                        <a href="javascript:history.back();">Previous Page</a>
                                        <br><br>
                                    </font>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </td>

            </tr>
        </tbody>
    </table>
</form>
<script>
    // Refresh parent window
// window.opener.location.href = window.opener.location.href;

function validate() {
    var y = document.form1.S1.value;
    if (y.length > 2000) {
        alert("Comments have too many characters. Maximum Characters: 2000.");
        document.form1.S1.select();
        document.form1.S1.focus();
        return false;
    }

    var form_name = "form1";
    var form_obj = document.getElementById(form_name);

    var metaresult = checkCharsBus(form_obj);
    if (!metaresult) return false;

    return true;
}

</script>