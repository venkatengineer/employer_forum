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

/* ---------- SECURITY CHECK ---------- */

/* Employer can only access their employees */
if($role == 'employer'){
    $check = $conn->prepare("SELECT id FROM users WHERE id=? AND employer_id=?");
    $check->bind_param("ii",$employee_id,$viewer_id);
    $check->execute();
    $check->store_result();

    if($check->num_rows == 0){
        die("Access Denied");
    }
}

/* Employee can only access himself */
if($role == 'employee' && $viewer_id != $employee_id){
    die("Access Denied");
}

/* ---------- ADD TASK (EMPLOYER ONLY) ---------- */
if(isset($_POST['add_task']) && $role == 'employer'){

    $title    = $_POST['title'];
    $desc     = $_POST['description'];
    $deadline = $_POST['deadline'];

    $stmt = $conn->prepare(
        "INSERT INTO tasks (title,description,assigned_by,assigned_to,deadline)
         VALUES (?,?,?,?,?)"
    );
    $stmt->bind_param("ssiis",$title,$desc,$viewer_id,$employee_id,$deadline);
    $stmt->execute();
}

/* ---------- UPDATE STATUS (EMPLOYEE ONLY) ---------- */
if(isset($_POST['update_task']) && $role == 'employee'){

    $task_id = intval($_POST['task_id']);
    $status  = $_POST['status'];
    $remark  = $_POST['remark'];

    $stmt = $conn->prepare(
        "UPDATE tasks SET status=? WHERE id=? AND assigned_to=?"
    );
    $stmt->bind_param("sii",$status,$task_id,$viewer_id);
    $stmt->execute();

    if(!empty($remark)){
        $rep = $conn->prepare(
            "INSERT INTO task_reports (task_id,employee_id,report)
             VALUES (?,?,?)"
        );
        $rep->bind_param("iis",$task_id,$viewer_id,$remark);
        $rep->execute();
    }
}

/* ---------- LOAD EMPLOYEE ---------- */
$user  = $conn->query("SELECT name FROM users WHERE id=$employee_id")->fetch_assoc();

/* ---------- LOAD TASKS ---------- */
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

/* PAGE */
body{
    margin:0;
    font-family:Helvetica, Arial, sans-serif;
    background:var(--cream);
}

/* HEADER */
.top{
    background:var(--blue);
    color:white;
    padding:20px 40px;
    font-size:20px;
    letter-spacing:2px;
}

/* CONTENT */
.container{
    width:75%;
    margin:50px auto;
}

/* CARD */
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

/* FORM */
.content{
    padding:22px;
}

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
    padding:12px 20px;
    margin-top:12px;
    cursor:pointer;
}

/* TASK ROW */
.task{
    border-top:2px solid #eee;
    padding:24px;
    display:flex;
    justify-content:space-between;
    gap:30px;
}

/* LEFT SIDE */
.task-info{ flex:1; }

.task-title{
    font-size:18px;
    font-weight:bold;
}

.meta{
    font-size:13px;
    color:#666;
    margin-top:5px;
}

/* STATUS */
.status-row{
    display:flex;
    align-items:center;
    margin-top:12px;
    font-weight:bold;
}

.status-light{
    width:18px;
    height:18px;
    border-radius:50%;
    margin-right:10px;
}

/* Traffic colors */
.not_started{ background:#FF2B2B; }
.in_progress{ background:#FFC107; }
.completed{ background:#28A745; }

/* RIGHT SIDE */
.task-actions{
    width:220px;
}
</style>
</head>

<body>

<div class="top">TASKS → <?php echo htmlspecialchars($user['name']); ?></div>

<div class="container">

<!-- EMPLOYER ASSIGN FORM -->
<?php if($role == 'employer'){ ?>
<div class="card">
<h3>Assign New Task</h3>
<div class="content">
<form method="POST">
<input type="text" name="title" placeholder="Task Title" required>
<textarea name="description" placeholder="Description"></textarea>
<input type="date" name="deadline">
<button name="add_task">Assign Task</button>
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
<div class="task-title"><?php echo htmlspecialchars($t['title']); ?></div>

<div class="meta"><?php echo nl2br(htmlspecialchars($t['description'])); ?></div>
<div class="meta">Deadline: <?php echo $t['deadline'] ?: '—'; ?></div>

<div class="status-row">
<div class="status-light <?php echo $t['status']; ?>"></div>
<?php echo strtoupper(str_replace('_',' ',$t['status'])); ?>
</div>
</div>

<?php if($role == 'employee'){ ?>
<div class="task-actions">
<form method="POST">
<input type="hidden" name="task_id" value="<?php echo $t['id']; ?>">

<select name="status">
<option value="not_started">Not Started</option>
<option value="in_progress">In Progress</option>
<option value="completed">Completed</option>
</select>

<textarea name="remark" placeholder="Progress update"></textarea>

<button name="update_task">Update</button>
</form>
</div>
<?php } ?>

</div>
<?php } ?>

</div>
</div>

</body>
</html>
