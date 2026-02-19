<?php
session_start();
include "../config.php";

if($_SESSION['role'] != 'employer'){
    die("Unauthorized");
}

$title    = $_POST['title'];
$desc     = $_POST['description'];
$deadline = $_POST['deadline'];
$employee = intval($_POST['employee_id']);
$viewer   = $_SESSION['user_id'];

$stmt = $conn->prepare(
    "INSERT INTO tasks (title,description,assigned_by,assigned_to,deadline)
     VALUES (?,?,?,?,?)"
);
$stmt->bind_param("ssiis",$title,$desc,$viewer,$employee,$deadline);
$stmt->execute();

header("Location: ../view_tasks.php?id=".$employee);
exit;
