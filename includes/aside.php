<div class="col-sm-4 pull-right">
<?php
    if(logged_in() === true) {
        include 'includes/widgets/loggedin.php';
    }else {
        include 'includes/widgets/login.php'; 
    }
//    include 'includes/widgets/user_count.php';
    ?>
</div>