<?php
session_start();
include "config.php";
include "includes/nav.php";

if(!isset($_SESSION['user_id'])){
    die("Login Required");
}

$viewer_id   = $_SESSION['user_id'];
$role        = $_SESSION['role'];
$employee_id = intval($_GET['id']);

/* SECURITY CHECK */
if($role == 'employer'){
    $check = $conn->prepare("SELECT id FROM users WHERE id=? AND employer_id=?");
    $check->bind_param("ii",$employee_id,$viewer_id);
    $check->execute();
    $check->store_result();

    if($check->num_rows == 0){
        die("Access Denied");
    }
}

if($role == 'employee' && $viewer_id != $employee_id){
    die("Access Denied");
}

/* LOAD DATA */
$user  = $conn->query("SELECT name FROM users WHERE id=$employee_id")->fetch_assoc();

$tasks = $conn->query(
    "SELECT * FROM tasks WHERE assigned_to=$employee_id ORDER BY created_at DESC"
);
?>
<!DOCTYPE html>
<html>
<head>
<title>Tasks</title>

<style>
:root{
    --blue:#0047FF;
    --red:#FF2B2B;
    --cream:#f7f6f2;
}

body{
    margin:0;
    font-family:Helvetica, Arial;
    background:var(--cream);
}

.top{
    background:var(--blue);
    color:white;
    padding:20px 40px;
    font-size:20px;
}

.container{
    width:75%;
    margin:50px auto;
}

.card{
    border:4px solid var(--blue);
    margin-bottom:35px;
    background:white;
}

.card h3{
    margin:0;
    background:var(--blue);
    color:white;
    padding:14px 22px;
}

.content{ padding:22px; }

input,textarea,select{
    width:100%;
    padding:12px;
    margin-top:10px;
    border:2px solid #ddd;
}

button{
    background:var(--red);
    color:white;
    border:none;
    padding:10px 16px;
    cursor:pointer;
}

.task{
    border-top:2px solid #eee;
    padding:24px;
    display:flex;
    justify-content:space-between;
    gap:30px;
}

.task-info{ flex:1; }

.status-light{
    width:18px;
    height:18px;
    border-radius:50%;
    display:inline-block;
    margin-right:8px;
}

.not_started{ background:#FF2B2B; }
.in_progress{ background:#FFC107; }
.completed{ background:#28A745; }

.task-actions{ width:240px; }
</style>
</head>

<body>

<div class="top">TASKS → <?php echo htmlspecialchars($user['name']); ?></div>

<div class="container">
    <?php if($role == 'employer'){ ?>
<a href="employee_analytics.php?id=<?php echo $employee_id; ?>"
style="display:inline-block;margin-bottom:20px;
background:#0047FF;color:white;padding:10px 18px;text-decoration:none;">
View Analytics
</a>
<?php } ?>


<!-- EMPLOYER ADD TASK -->
<?php if($role == 'employer'){ ?>
<div class="card">
<h3>Assign Task</h3>
<div class="content">
<form method="POST" action="actions/add_task.php">
<input type="hidden" name="employee_id" value="<?php echo $employee_id; ?>">

<input type="text" name="title" placeholder="Task Title" required>
<textarea name="description"></textarea>
<input type="date" name="deadline">

<button type="submit">Assign</button>
</form>
</div>
</div>
<?php } ?>

<!-- TASK LIST -->
<div class="card">
<h3>Assigned Tasks</h3>

<?php while($t = $tasks->fetch_assoc()){ ?>
<div class="task">

<div class="task-info">
<b><?php echo htmlspecialchars($t['title']); ?></b><br>
<?php echo nl2br(htmlspecialchars($t['description'])); ?><br>
Deadline: <?php echo $t['deadline'] ?: '—'; ?><br>

<span class="status-light <?php echo $t['status']; ?>"></span>
<?php echo strtoupper(str_replace('_',' ',$t['status'])); ?>
</div>

<div class="task-actions">

<?php if($role == 'employee'){ ?>
<form method="POST" action="actions/update_task.php">
<input type="hidden" name="task_id" value="<?php echo $t['id']; ?>">

<select name="status">
<option value="not_started">Not Started</option>
<option value="in_progress">In Progress</option>
<option value="completed">Completed</option>
</select>

<textarea name="remark"></textarea>
<button>Update</button>
</form>
<?php } ?>

<?php if($role == 'employer'){ ?>
<form method="POST" action="actions/edit_task.php">
<input type="hidden" name="task_id" value="<?php echo $t['id']; ?>">
<input type="text" name="title" value="<?php echo htmlspecialchars($t['title']); ?>">
<input type="date" name="deadline" value="<?php echo $t['deadline']; ?>">
<button>Save</button>
</form>

<br>

<a href="actions/delete_task.php?task_id=<?php echo $t['id']; ?>"
onclick="return confirm('Delete task?')">Delete</a>
<?php } ?>

</div>

</div>
<?php } ?>

</div>
</div>

</body>
</html>
