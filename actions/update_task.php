<?php
session_start();
include "../config.php";

if($_SESSION['role'] != 'employee'){
    die("Unauthorized");
}

$task_id = intval($_POST['task_id']);
$status  = $_POST['status'];
$remark  = $_POST['remark'];
$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare(
    "UPDATE tasks SET status=? WHERE id=? AND assigned_to=?"
);
$stmt->bind_param("sii",$status,$task_id,$user_id);
$stmt->execute();

if(!empty($remark)){
    $rep = $conn->prepare(
        "INSERT INTO task_reports (task_id,employee_id,report)
         VALUES (?,?,?)"
    );
    $rep->bind_param("iis",$task_id,$user_id,$remark);
    $rep->execute();
}

header("Location: ../view_tasks.php?id=".$user_id);
exit;
