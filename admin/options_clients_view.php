<?php 
    require_once('../config.php');
    require_once('../lib.php');

    $customer_id = -1;
    
    if ($_SERVER["REQUEST_METHOD"] == "POST") {

        $user = $_SESSION['User'];
        if ($user['UserId'] == 0){ //excption handling
	    	header('Location: '. SECURE_URL . LOGIN_PAGE, true, 301);
		   	exit(0);
	    }
        //__debug($_POST);
        $customer_id = $_POST["customer_id"];
        // Retrieve the content of the textarea
        if ($_POST["profile-comment-type"] == 'Add'){
            $textareaContent = $_POST["profile-comment"];
            
            $currentDateTime = date('Y-m-d H:i:s');
            $randomID = str_pad(rand(1000, 9999), 4, '0', STR_PAD_LEFT);

            $userid = $user['UserId'];
            if (strlen($textareaContent) > 0)
            {
                addCustomerComment($customer_id, [
                    'id' => $randomID,
                    'user_id' => $userid,
                    'datetime' => $currentDateTime,
                    'content' => $textareaContent
                ]);
            }
        }
        if ($_POST["profile-comment-type"] == 'Update'){
            $comment_id = $_POST["profile-comment-oldid"];
            $textareaContent = $_POST["profile-comment-update-$comment_id"];
            $currentDateTime = date('Y-m-d H:i:s');

            updateCustomerComment($customer_id, $comment_id, $currentDateTime, $textareaContent);
        }

        if ($_POST["profile-comment-type"] == 'Delete'){
            $comment_id = $_POST["profile-comment-oldid"];
            deleteCustomerComment($customer_id, $comment_id);
        }
        header('Location: '. SECURE_URL . "/admin/options_clients_view.php?customer_id=".$customer_id, true, 301);

        // Display the submitted content
    }

    if ($customer_id == -1) {
        if (isset($_GET['customer_id'])) {
            // Extract the value of startDate
            $customer_id = $_GET['customer_id'];
        }else {
            header('Location: '. SECURE_URL . ADMIN_INDEX, true, 301);
            exit; // Make sure to exit after redirection to prevent further script execution
        }
    }
    
    $userInfo = getUserInfo();

    $cutomer_info = getCutomerInfoById($customer_id);
    if (!isset($cutomer_info["businessName"]) || !isset($cutomer_info["email"])) {//exception handling
        header('Location: '. SECURE_URL . ADMIN_INDEX, true, 301);
        exit; // Make sure to exit after redirection to prevent further script execution
    }
    
    $addressArray = json_decode($cutomer_info["postalAddr"], true);
    if ($addressArray === null) {
        $street = "";
        $city = "";
        $state = "";
        $postcode = "";
    }
    $street = $addressArray['street'];
    $city = $addressArray['city'];
    $state = $addressArray['state'];
    $postcode = $addressArray['postcode'];

    $comments_array = json_decode($cutomer_info["comments"], true);
    
    $commnetsShow = false;
    if (is_array($comments_array) && count($comments_array) > 0) {
        $commnetsShow = true;
    }

    $regDate = date('l, F jS, Y', strtotime($cutomer_info["regDate"]));
?>
<form name="form1" id="profile-comments" method="POST" action="options_clients_view.php">
    <input type="hidden" name="customer_id" value="<?php echo $customer_id;?>">
    <table border="0" width="100%" cellspacing="1" cellpadding="3" bgcolor="#000080">
        <tr>
            <td width="100%" bgcolor="#C5D4F0" align="left" valign="top">
                <font face="Arial" size="2" color="#000000"><b>Customer Profile</b> </font>
            </td>
        </tr>
        <tr>
            <td width="100%" bgcolor="#FFFFFF" valign="top" align="left">
                <table border="0" cellpadding="5" width="100%" cellspacing="1" bgcolor="navy">
                    <tr>
                        <td width="100%" bgcolor="#FFFFFF" valign="top" align="left">
                            <font face="Arial" size="2" color="#000000">
                                <br>
                                <!-- <a href="/apd/options/options_clients_ed.asp?c=5165FA94-5910-4BFD-8268-F4BC4D44A149&amp;win=1">Update Customer</a>
                                <br>
                                <a href="/apd/options/options_clients_bookings1.asp?c=5165FA94-5910-4BFD-8268-F4BC4D44A149&amp;s=1">View Booking History</a> -->
                                <br><br>
                                <b><?php echo $cutomer_info["businessName"];?></b>
                            </font>
                            <p>
                                <font face="Arial" size="2" color="#000000">Business Name : <?php echo $cutomer_info["businessName"];?></font>
                            </p>
                            <p>
                                <font face="Arial" size="2" color="#000000">Email Address : <?php echo $cutomer_info["email"];?></font>
                            </p>
                            <p>
                                <font face="Arial" size="2" color="#000000">Postal Address : <?php echo $street;?> &nbsp;<?php echo $street;?>&nbsp;<?php echo $state;?> &nbsp;<?php echo $postcode;?>&nbsp;Australia</font>
                            </p>
                            <p>
                                <font face="Arial" size="2" color="#000000">Phone : <?php echo $cutomer_info["phone"];?></font>
                            </p>
                            <p>
                                <font face="Arial" size="2" color="#000000">Customer since <?php echo $regDate;?></font>
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td bgcolor="#E8EEF7" valign="top" align="left" colspan="2">
                            
                            <input type="hidden" id="profile-comment-type" name="profile-comment-type" value="Add">
                            <input type="hidden" id="profile-comment-oldid" name="profile-comment-oldid" value="">
                            <script type="text/javascript" src="/admin/js/jquery.min.js"></script>
                            <script type="text/javascript">
                                // JavaScript functions here
                                
                            </script>
                            <style type="text/css">
                                /* CSS styles here */

                            </style>
                            <font face="Arial" size="2" color="#000000">Enter New Comments to Attach to Profile (Optional). Comments do not display to Customer.</font>
                        </td>
                    </tr>
                    <tr>
                        <td bgcolor="#FFFFFF" valign="top" align="left" colspan="2">
                            <font face="Arial" size="2" color="#000000">
                                <textarea id="profile-comment-add" name="profile-comment" rows="4" cols="55" maxlength="2000" data-listener-added_2b40e602="true"></textarea>
                                <br>
                            </font>
                            <font face="Arial" size="1" color="#000000">Maximum Characters: 2000</font>
                        </td>
                    </tr>
                    <tr>
                        <td width="100%" align="left" bgcolor="#FFFFFF">
                            <font face="Arial" size="2" color="#000000">
                                <br>
                                <input type="submit" value="Add Comment" onclick="doSubmit(event, 'Add', '');">&nbsp;&nbsp;&nbsp;
                                <br>
                                <br>
                            </font>
                        </td>
                    </tr>
                    <?php if ($commnetsShow): ?>
                        <tr>
                            <td width="100%" bgcolor="#FFFFFF" valign="top" align="left">
                                <table border="0" cellpadding="3" width="100%" cellspacing="1" bgcolor="silver">
                                    <tbody><tr>
                                        <td width="100%" bgcolor="#E8EEF7" align="left" valign="middle" colspan="3">
                                            <font face="Arial" size="2" color="#000000">
                                                <strong>Comments Attached to <?php echo $cutomer_info["businessName"];?></strong>
                                            </font>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td width="90%" bgcolor="#E8EEF7" align="left" valign="middle">
                                            <font face="Arial" size="2" color="#000000">Comment</font>
                                        </td>
                                        <td width="10%" bgcolor="#E8EEF7" align="center" valign="middle">
                                            <font face="Arial" size="2" color="#000000">Action</font>
                                        </td>
                                    </tr>

                                    <?php foreach ($comments_array as $comment): ?>
                                    <tr id="comment-row-219817793">
                                        <td width="90%" bgcolor="#FFFFFF" align="left" valign="top">
                                            <font face="Arial" size="2" color="#000000">
                                                <?php 
                                                if ($comment["datetime"] != null ){
                                                    $converted_date_time = date('D j M Y g:i A', strtotime($comment["datetime"]));
                                                    echo $converted_date_time;
                                                }
                                               ?> - Added by 
                                            
                                                <?php
                                                echo $userInfo[$comment["user_id"]];
                                                ?>    
                                                <div id="comment-id-<?php echo $comment["id"];?>"><?php echo $comment["content"];?></div>
                                                <div id="comment-up-<?php echo $comment["id"];?>" class="comment-update" style = "display: none">
                                                    <textarea id="profile-comment-update-<?php echo $comment["id"];?>" name="profile-comment-update-<?php echo $comment["id"];?>" rows="4" cols="55" maxlength="2000"><?php echo $comment["content"];?></textarea><br><br>
                                                    <input type="submit" value="Update Comment" onclick="doSubmit(event, 'Update', <?php echo $comment["id"];?>);">
                                                    <input type="button" value="Cancel" onclick="commentUpdate('#comment-uc-<?php echo $comment["id"];?>', '<?php echo $comment["id"];?>')">
                                                </div>
                                            </font>
                                        </td>
                                        <td width="10%" bgcolor="#FFFFFF" align="center" valign="top">
                                            <font face="Arial" size="2" color="#000000">
                                                <p style="margin:0;"><a id="comment-uc-<?php echo $comment["id"];?>" class="comment-uc" href="javascript:void(0);" onclick="commentUpdate(this, '<?php echo $comment["id"];?>');">Update</a></p>
                                                <p style="margin:0;"><a class="comment-dc" href="javascript:void(0);" onclick="commentDelete(event, this, '<?php echo $comment["id"];?>');">Delete</a></p>
                                            </font>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody></table>
                            </td>
                        </tr>
                    <?php endif; ?>
                    <tr>
                        <td width="100%" align="left" valign="top" bgcolor="#FFFFFF">
                            <font face="Arial" size="2" color="#000000">
                                <br>
                                <input type="button" value="Print" onclick="javascript:printPage()">&nbsp;&nbsp;&nbsp;
                                <input type="button" value="Close" onclick="javascript:self.close();">
                                <br>
                                <br>
                            </font>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

</form>
<script>
    // Refresh parent window
// window.opener.location.href = window.opener.location.href;

var prevComm = '';

function commentUpdate(_this, cID) {
    var toggle = $(_this).text() == 'Update';

    $('#comment-up-' + cID).css({ 'display': (toggle) ? 'block' : 'none' });

    if (toggle) {
        prevComm = $('#profile-comment-update-' + cID).val();

        var textarea = document.getElementById('profile-comment-update-' + cID);
        moveCursorToEnd(textarea);

        $(window).scrollTop($('#comment-up-' + cID).offset().top);
    } else {
        $('#profile-comment-update-' + cID).val(prevComm);
        prevComm = '';
    }

    $(_this).text((toggle) ? 'Cancel' : 'Update');

    $('#comment-id-' + cID).css({ 'display': (toggle) ? 'none' : 'block' });
    
    $('.comment-uc:not(#comment-uc-' + cID + ')').css({ 'display': (toggle) ? 'none' : 'block' });
    $('.comment-dc').css({ 'display': (toggle) ? 'none' : 'block' });
}

function moveCursorToEnd(el) {
    if (typeof el.createTextRange != 'undefined') {
        el.focus();
        var range = el.createTextRange();
        range.collapse(false);
        range.select();
    }
}

function commentDelete(e, _this, cID) {
    $('#comment-row-' + cID).addClass('highlight');

    var conf = confirm('Are you sure you want to delete this comment?');
    if (conf) {
        console.log(1234);
        doSubmit(e, 'Delete', cID);
        $('#profile-comments').submit();
    }
    $('.highlight').removeClass('highlight');
}

function doSubmit(e, type, ocID) {
    if (type == 'Add') {
        if ($('#profile-comment-add').val() == '') {
            e.preventDefault();
            alert('Please enter a comment first.');
        }
    } else if (type == 'Update') {
        if ($('#profile-comment-update-' + ocID).val() == prevComm) {
            e.preventDefault();
            alert('You have not changed the comment.');
        } else if ($('#profile-comment-update-' + ocID).val() == '') {
            e.preventDefault();
            alert('Please enter a comment first.');
        }
    }

    $('#profile-comment-type').val(type);
    $('#profile-comment-oldid').val(ocID);
}


function printPage(){
    if (window.print)
        window.print()
    else
        alert ("Sorry, your browser doesn't support this feature. Use File/Print instead.");
}

</script>