<?php

if (isset($_FILES["file"])) {
    $errors = array();
    $allowed_ext = array('jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx');

    if ($_FILES["file"]["error"] > 0) {
        echo "An error ocurred when uploading.<br />";
    } else {
        if (!in_array(strtolower(end(explode('.', $_FILES["file"]["name"]))), $allowed_ext)) {
            $errors[] = 'Extension not allowed!<br />';
        }
        if ($_FILES["file"]["size"] > 5242880) {
            $errors[] = 'Sorry, file size must be under 0MB!<br />';
        }

        if (empty($errors)) {
            echo 'File uploaded!';
            echo "Stored file: " . $_FILES["file"]["name"] . "<br/>";
            echo "Size: " . ($_FILES["file"]["size"] / 1024) . " kB<br>";
            move_uploaded_file($_FILES["file"]["tmp_name"], 'upload/' . $_FILES["file"]["name"]);
        } else {
            echo output_errors($errors);
        }
    }
}
?>
