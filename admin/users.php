<?php
    require_once('header.php');

    // save action for new
    if( isset($_POST['Submit']) ) {
    }

    $db = getDBConnection();
    $stmt = $db->prepare("SELECT UserId, Username, Firstname, Lastname, Email, UserType, Active FROM users");
    $stmt->execute();
    $stmt->bind_result($userId, $username, $firstname, $lastname, $email, $usertype, $active);
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
?>
        <h4 class="page-title">Existing Users</h4>
        <div class="table-responsive">
            <table border="0" cellspacing="0" cellpadding="5" width="100%" class="table">
                <tr>
                    <td width="150" nowrap>Username</td>
                    <td width="100" nowrap>First name</td>
                    <td width="100" nowrap>Last name</td>
                    <td width="100" nowrap>Email</td>
                    <td width="20" nowrap>Type</td>
                    <td width="20" nowrap>Active</td>
                </tr>
                <?php
                while ($stmt->fetch()) {
                    ?>
                    <tr>
                        <td><a href="#?userid=<?php echo $userId ?>"><?php echo $username ?></a></td>
                        <td><?php echo $firstname ?></td>
                        <td><?php echo $lastname ?></td>
                        <td><?php echo $email ?></td>
                        <td><?php echo $usertype ?></td>
                        <td><?php echo $active ?></td>
                    </tr>
                    <?php
                }
                ?>
                <tr>
                    <td align="right" colspan="6"><a href="#" data-toggle="modal" data-target="#exampleModal">Add New User</a></td>
                </tr>
            </table>
        </div>
    <?php
    }
    $stmt->close();
    $db->close();
    
    require_once('footer.php');
?>

<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <form method="post" class="form-horizontal" id="APP_FORM">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Add New User</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="Username">Username *</label>
                        <input type="input" class="form-control required" required="required" id="Username" placeholder="Username" name="username"/>
                    </div>
                    <div class="form-group">
                        <label for="password">Password *</label>
                        <input type="password" class="form-control required" required="required" id="password" placeholder="Password" name="password"/>
                    </div>
                    <div class="form-group">
                        <label for="password_confirm">Confirm Password *</label>
                        <input type="password" class="form-control required" required="required" id="password_confirm" placeholder="Confirm Password" name="password_confirm"/>
                    </div>
                    <div class="form-group">
                        <label for="FirstName">First Name *</label>
                        <input type="input" class="form-control required" required="required" id="FirstName" placeholder="First Name" name="first_name"/>
                    </div>
                    <div class="form-group">
                        <label for="LastName">Last Name *</label>
                        <input type="input" class="form-control required" required="required" id="LastName" placeholder="Last Name" name="last_name"/>
                    </div>
                    <div class="form-group">
                        <label for="email_addr">Email *</label>
                        <input type="input" class="form-control required" required="required" id="email_addr" placeholder="Email" name="email_addr"/>
                    </div>
                    <div class="form-group">
                        <label for="UserType">User Type</label>
                        <select name="user_type" id="UserType" class="form-select form-select-sm">
                            <option value="U">User</option>
                            <option value="A">Administrator</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="IsActive">Active</label>
                        <input class="" type="checkbox" id="IsActive" name="active" value="Y" />
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary btn-sm" name="Save" value="Save" disabled>Save</button>
                </div>
            </div>
        </div>
    </form>
</div>