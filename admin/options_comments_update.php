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
        $textareaContent = $_POST["S1"];
        $booking_code = $_POST["bookingCode"];
        $currentDateTime = date('Y-m-d H:i:s');
        $id = $_POST["commentId"];
        $comment_id = $_POST["commentId"];
        updateBookingComments($booking_code, $comment_id, $currentDateTime, $textareaContent);
        
        header('Location: '. SECURE_URL . "/admin/options_comments.php?booking_code=".$booking_code, true, 301);

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
<form name="form1" method="POST" action="options_comments_update.php" onsubmit="return validate()">
    <input type="hidden" name="bookingCode" value="<?php echo $booking_code;?>">
    <input type="hidden" name="commentId" value="<?php echo $comment_id;?>">

    <table border="0" cellpadding="3" width="100%" cellspacing="1" bgcolor="navy">
        
        <tbody>
            <tr>
                <td width="75%" bgcolor="#C5D4F0" align="center" valign="middle"><font face="Arial" size="2" color="#000000"><b>
                    Comments
                </b></font></td>
            </tr>
            <tr>
                <td width="100%" bgcolor="#FFFFFF" valign="top" align="left">
                    <div align="center">
                        <center>
                            <table border="0" cellpadding="5" cellspacing="0" width="100%">
                                <tbody>
                                    <tr>
                                        <td valign="top">
                                            <font face="Arial" size="2" color="#000000">&nbsp;<b><?php  echo $booking_info["businessName"];?> </b>
                                                <br>
                                                &nbsp;Booking Date : </font>
                                            <font face="Arial" size="2" color="#000000"> <?php  echo $bookingDate;?><br>
                                            </font>
                                            <font face="Arial" size="2" color="#000000">&nbsp;Booking Time : </font>
                                            <font face="Arial" size="2" color="#000000"><?php  echo $bookingTime;?> <br>
                                            </font>
                                        </td>
                                        <td valign="bottom">
                                            <font face="Arial" size="2" color="#000000">
                                            </font>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </center>
                    </div>
                </td>
            </tr>

            <tr>
                <td width="100%" bgcolor="#E8EEF7" align="left" valign="top">
                    <font color="navy" size="2" face="arial">Update Comments of 
                        <?php 
                            $converted_date_time = date('D j M Y g:i A', strtotime($dateTime));
                            echo $converted_date_time;?>
                            Attach to Booking</font>
                </td>
            </tr>
            <tr>
                <td width="100%" bgcolor="#FFFFFF" valign="top" align="left">
                    <font face="Arial" size="2" color="#000000"></font>
                    <textarea rows="8" name="S1" cols="55"><?php echo $content;?></textarea><br>
                    <font face="Arial" size="1" color="#000000">Maximum Characters 2000</font>
                </td>
            </tr>

            <tr>

                <td width="100%" align="left" bgcolor="#FFFFFF">
                    <font face="Arial" size="2" color="#000000">
                        <br>
                        <input type="submit" value="Save">&nbsp;&nbsp;&nbsp;

                        <input type="button" value="Close to Refresh Calendar"
                            onclick="javascript:self.close();window.opener.location.reload();">&nbsp;&nbsp;&nbsp;

                        <!--//  &nbsp;&nbsp;&nbsp;
                        //-->
                        <br>
                        <br>
                        <a href="javascript:history.back();">Previous Page</a>
                        <br>
                        <br>
                    </font>
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