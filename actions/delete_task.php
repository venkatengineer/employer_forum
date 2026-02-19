<?php
session_start();
include "../config.php";

if($_SESSION['role'] != 'employer'){
    die("Unauthorized");
}

$task_id = intval($_GET['task_id']);

$conn->query("DELETE FROM tasks WHERE id=$task_id");

header("Location: ".$_SERVER['HTTP_REFERER']);
exit;
