<?php
include 'core/init.php';
protect_page();

if (empty($_POST) === false) {
    $required_fields = array('current_password', 'password', 'password_again');
    foreach ($_POST as $key => $value) {
        if (empty($value) && in_array($key, $required_fields) === true) {
            $errors[] = 'Field marked with asterisk are required!';
            break 1;
        }
    }

    if (md5($_POST['current_password']) === $user_data['password']) {
        if (trim($_POST['password']) !== trim($_POST['password_again'])) {
            $errors[] = 'Your new passwords do not match!';
        } else if (strlen($_POST['password']) < 6) {
            $errors[] = 'Your password must be at least 6 characters.';
        }
    } else {
        $errors[] = 'Your current password is incorect!';
    }
}

include 'includes/overall/header.php'; ?>
<div class="col-sm-8">
    <h2>Change Password</h2>

<?php
if (isset($_GET['success']) === true && empty($_GET['success']) === true) {
    echo 'Your password has been changed.';
} else {
    if (isset($_GET['force']) === true && empty($_GET['force']) === true) {
        ?>
        <p>Now, you have to change your password!</p>
    <?php
    }
    if (empty($_POST) === false && empty($errors) === true) {
        change_password($session_user_id, $_POST['password']);
        header("Location: changepassword.php?success");
    } else if (empty($errors) === false) {
        echo output_errors($errors);
    }
    ?>

    <form role="form" action="" method="post">
        <div class="form-group">
            <label>Current password*:</label>
            <input class="form-control" type="password" name="current_password">
        </div>
        <div class="form-group">
            <label>New password*:</label>
            <input class="form-control" type="password" name="password">
        </div>
        <div class="form-group">
            <label>New password again*:</label>
            <input class="form-control" type="password" name="password_again">
        </div>
        <button class="btn btn-default" type="submit" value="Change password">Change Password</button>
    </form>
    </div>

<?php
}
include 'includes/overall/footer.php';
?>