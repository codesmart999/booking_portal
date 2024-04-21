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
        //__debug( $_POST);
        // Retrieve the content of the textarea
        $textareaContent = $_POST["S1"];
        $booking_code = $_POST["bookingCode"];
        $attended = ($_POST["att"] == 'Y') ? 1 : 0;
        $currentDateTime = date('Y-m-d H:i:s');
        $randomID = str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT);
        $userid = $user['UserId'];
        if (strlen($textareaContent) > 0)
        {
            addBookingComments($booking_code, $attended, [
                'id' => $randomID,
                'user_id' => $userid,
                'datetime' => $currentDateTime,
                'content' => $textareaContent,
            ]);
        }else {
            addBookingComments($booking_code, $attended, []);
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

    $bookingTime = "$newStartTime - $newEndTime";

    $customerCommnetsShow = false;
    $systemCommnetsShow = false;
    if (is_array($comments_array) && count($comments_array) > 0) {
        foreach ($comments_array as $comment){
            if (isset($comment["type"]))
                $systemCommnetsShow = true;
            else
                $customerCommnetsShow = true;
        }
    }
?>
<form name="form1" method="POST" action="options_comments.php" onsubmit="return validate()">
    <input type="hidden" name="bookingCode" value="<?php echo $booking_code;?>">

    <table id = "options_comments_table" border="0" cellpadding="3" width="100%" cellspacing="1" bgcolor="navy">
        
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
                                            <font face="Arial" size="2" color="#000000">&nbsp;Attended? </font>
                                            <font face="Arial" size="2" color="#000000">
                                                <input type="radio" name="att" value="Y" <?php echo ($booking_info["attended"] == 1) ? 'checked="checked"' : ''; ?>> Yes &nbsp;&nbsp;
                                                <input type="radio" name="att" value="N" <?php echo ($booking_info["attended"] == 0) ? 'checked="checked"' : ''; ?>> No </font>
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
                    <font color="navy" size="2" face="arial">Enter New Comments to Attach to Booking</font>
                </td>
            </tr>
            <tr>
                <td width="100%" bgcolor="#FFFFFF" valign="top" align="left">
                    <font face="Arial" size="2" color="#000000"></font>
                    <textarea rows="8" name="S1" cols="55"></textarea><br>
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
                    </font>
                </td>

            </tr>
            <?php if ($customerCommnetsShow): ?>
            <tr>
                <td width="100%" bgcolor="#FFFFFF" valign="top" align="left">
                
                    <table border="0" cellpadding="3" width="100%" cellspacing="1" bgcolor="silver">
                        <tbody>
                            <tr>
                                <td width="100%" bgcolor="#E8EEF7" align="left" valign="middle" colspan="3">
                                    <font face="Arial" size="2" color="#000000"><b>Comments Attached to Booking of <?php echo $bookingDate;?>  <?php echo $bookingTime;?> for <?php echo $booking_info["businessName"];?></b></font>
                                </td>
                            </tr>
                            <tr>
                                <td width="25%" bgcolor="#E8EEF7" align="left" valign="middle">
                                    <font face="Arial" size="2" color="#000000">Booking Date &amp; Time</font>
                                </td>
                                <td width="65%" bgcolor="#E8EEF7" align="left" valign="middle">
                                    <font face="Arial" size="2" color="#000000">Comment</font>
                                </td>
                                <td width="10%" bgcolor="#E8EEF7" align="center" valign="middle">
                                    <font face="Arial" size="2" color="#000000">Action</font>
                                </td>
                            </tr>
                            <?php foreach ($comments_array as $comment): 
                                            if(isset($comment["type"]))
                                                continue;
                                ?>
                                <tr>
                                    <td width="25%" bgcolor="#FFFFFF" align="left" valign="top">
                                        <font face="Arial" size="2" color="#000000">
                                            <?php echo $bookingDate;?><br><?php echo $bookingTime;?>
                                        </font>
                                    </td>
                                    <td width="65%" bgcolor="#FFFFFF" align="left" valign="top">
                                        <font face="Arial" size="2" color="#000000">
                                            <?php 
                                                $converted_date_time = date('D j M Y g:i A', strtotime($comment["datetime"]));
                                                echo $converted_date_time;?> - Added by 
                                            <?php
                                                echo $userInfo[$comment["user_id"]];
                                            ?>    
                                            <br><?php echo $comment["content"];?>
                                        </font>
                                    </td>
                                    <td width="10%" bgcolor="#FFFFFF" align="center" valign="top">
                                        <font face="Arial" size="2" color="#000000">
                                            <a href="/admin/options_comments_update.php?booking_code=<?php echo $booking_code;?>&comment_id=<?php echo $comment["id"];?>">Update</a><br>
                                            <a href="options_comments_delete.php?booking_code=<?php echo $booking_code;?>&comment_id=<?php echo $comment["id"];?>">Delete</a>
                                        </font>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
               
                </td>
            </tr>
            <?php endif; ?>

            <?php if ($systemCommnetsShow): ?>
            <tr>
                <td width="100%" bgcolor="#FFFFFF" valign="top" align="left">
                
                    <table border="0" cellpadding="3" width="100%" cellspacing="1" bgcolor="silver">
                        <tbody>
                            <tr>
                                <td width="100%" bgcolor="#E8EEF7" align="left" valign="middle" colspan="3">
                                    <font face="Arial" size="2" color="#000000"><b>System Comments Attached to Booking of <?php echo $bookingDate;?>  <?php echo $bookingTime;?> for <?php echo $booking_info["businessName"];?></b></font>
                                </td>
                            </tr>
                            <tr>
                                <td width="25%" bgcolor="#E8EEF7" align="left" valign="middle">
                                    <font face="Arial" size="2" color="#000000">Booking Date &amp; Time</font>
                                </td>
                                <td width="65%" bgcolor="#E8EEF7" align="left" valign="middle">
                                    <font face="Arial" size="2" color="#000000">Comment</font>
                                </td>
                            </tr>
                            <?php foreach ($comments_array as $comment): 
                                    if(!isset($comment["type"]))
                                                continue;
                                ?>
                                <tr>
                                    <td width="25%" bgcolor="#FFFFFF" align="left" valign="top">
                                        <font face="Arial" size="2" color="#000000">
                                            <?php echo $bookingDate;?><br><?php echo $bookingTime;?>
                                        </font>
                                    </td>
                                    <td width="65%" bgcolor="#FFFFFF" align="left" valign="top">
                                        <font face="Arial" size="2" color="#000000">
                                            <?php 
                                                $converted_date_time = date('D j M Y g:i A', strtotime($comment["datetime"]));
                                                echo $converted_date_time;?><br>
                                            <?php echo getSystemCommentStringFromComment($comment);?>
                                             by <?php
                                                echo $userInfo[$comment["user_id"]];
                                            ?>    
                                           
                                        </font>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
               
                </td>
            </tr>
             <?php endif; ?>



            <tr>
                <td width="100%" bgcolor="#FFFFFF" align="right" valign="top" colspan="3">
                    <!-- <font face="Arial" size="2" color="#000000"><a
                            href="javascript:popUp('options_comments1b.asp?s=&amp;w=&amp;c=B7CD71CF-D48B-4916-985D-DF98D139EE2F');">
                            Comments Attached to Bookings for
                            Public Holiday</a> </font> -->
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