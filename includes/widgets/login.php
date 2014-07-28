<div class="col-md-12">
    <h2>Log in/Register</h2>

    <form role="form" action="login.php" method="post">
        <div class="form-group">
            <label>Username:</label>
            <input type="text" class="form-control" name="username">
        </div>
        <div class="form-group">
            <label>Password:</label>
            <input type="password" class="form-control" name="password">
        </div>
        <input type="submit" class="btn btn-default" value="Log in">

        <p>
            <a href="register.php">Register</a>
        </p>

        <p clas="help-block">
            Forgotten your <a href="recover.php?mode=username">username</a> or <a href="recover.php?mode=password">password</a>?
        </p>
    </form>
</div>