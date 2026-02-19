<?php
session_start();
include "../config.php";

if($_SESSION['role'] != 'employer'){
    die("Unauthorized");
}

$task_id  = intval($_POST['task_id']);
$title    = $_POST['title'];
$desc     = $_POST['description'];
$deadline = $_POST['deadline'];

$stmt = $conn->prepare(
    "UPDATE tasks SET title=?, description=?, deadline=? WHERE id=?"
);
$stmt->bind_param("sssi",$title,$desc,$deadline,$task_id);
$stmt->execute();

header("Location: ".$_SERVER['HTTP_REFERER']);
exit;
