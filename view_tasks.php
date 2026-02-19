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
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Tasks</title>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;700;900&display=swap" rel="stylesheet">

<style>
:root {
    --blue: #0047FF;
    --blue-dark: #0033CC;
    --red: #EE2A1B;
    --cream: #F7F6F2;
    --ink: #121212;
    --white: #FFFFFF;
    --ease: cubic-bezier(0.23, 1, 0.32, 1);
}

* { box-sizing: border-box; }

body {
    margin: 0;
    font-family: 'Outfit', Helvetica, Arial, sans-serif;
    background: var(--cream);
    color: var(--ink);
    overflow-x: hidden;
}

/* ===== TOP HEADER ===== */
.top {
    background: var(--blue);
    color: white;
    padding: 0 40px;
    height: 75px;
    font-size: 1.2rem;
    font-weight: 900;
    letter-spacing: 4px;
    text-transform: uppercase;
    display: flex;
    align-items: center;
    position: relative;
    overflow: hidden;
    animation: fadeDown 0.6s var(--ease) both;
}

.top::after {
    content: '';
    position: absolute;
    right: 0; top: 0;
    width: 80px; height: 100%;
    background: var(--red);
    clip-path: polygon(30% 0, 100% 0, 100% 100%, 0% 100%);
}

/* ===== CONTAINER ===== */
.container {
    max-width: 1100px;
    width: 90%;
    margin: 50px auto;
    padding-bottom: 60px;
}

/* ===== CARDS ===== */
.card {
    border: 4px solid var(--ink);
    margin-bottom: 30px;
    background: white;
    transition: all 0.45s var(--ease);
}

.card:hover {
    box-shadow: 10px 10px 0px var(--blue);
}

.card h3 {
    margin: 0;
    background: var(--blue);
    color: white;
    padding: 18px 28px;
    font-size: 1.05rem;
    font-weight: 700;
    letter-spacing: 2px;
    text-transform: uppercase;
    display: flex;
    align-items: center;
    gap: 12px;
}

.card h3::before {
    content: '';
    width: 14px; height: 14px;
    background: var(--red);
    border-radius: 50%;
    flex-shrink: 0;
    animation: pulse 2s infinite;
}

.content {
    padding: 28px;
}

/* ===== FORM ELEMENTS ===== */
input, textarea, select {
    width: 100%;
    padding: 14px;
    margin-top: 12px;
    border: 3px solid #e0e0e0;
    font-family: 'Outfit', sans-serif;
    font-size: 0.95rem;
    transition: border-color 0.3s;
    background: var(--cream);
}

input:focus, textarea:focus, select:focus {
    outline: none;
    border-color: var(--blue);
}

button {
    background: var(--red);
    color: white;
    border: none;
    padding: 12px 24px;
    cursor: pointer;
    font-family: 'Outfit', sans-serif;
    font-size: 0.95rem;
    font-weight: 700;
    letter-spacing: 1px;
    text-transform: uppercase;
    margin-top: 15px;
    transition: all 0.3s var(--ease);
}

button:hover {
    background: var(--ink);
    transform: translateY(-2px);
    box-shadow: 4px 4px 0px var(--red);
}

/* ===== TASK ROWS ===== */
.task {
    border-top: 2px solid #eee;
    padding: 24px 28px;
    display: flex;
    justify-content: space-between;
    gap: 30px;
    transition: all 0.35s var(--ease);
    position: relative;
}

.task::before {
    content: '';
    position: absolute;
    left: 0; top: 0;
    width: 0; height: 100%;
    background: var(--blue);
    transition: width 0.3s ease;
}

.task:hover {
    background: #f5f5ff;
    padding-left: 38px;
}

.task:hover::before {
    width: 6px;
}

.task-info {
    flex: 1;
    line-height: 1.7;
}

.task-info b {
    font-size: 1.05rem;
    letter-spacing: 0.5px;
}

/* ===== STATUS BADGES ===== */
.status-light {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    display: inline-block;
    margin-right: 6px;
    vertical-align: middle;
    animation: pulse 2.5s infinite;
}

.status-badge {
    display: inline-block;
    padding: 6px 18px;
    border-radius: 0;
    font-size: 0.75rem;
    font-weight: 900;
    letter-spacing: 2px;
    text-transform: uppercase;
    margin-top: 10px;
    border: 3px solid;
}

.status-badge.badge-not_started {
    background: var(--red);
    color: var(--white);
    border-color: var(--red);
    box-shadow: 0 0 15px rgba(238, 42, 27, 0.3);
}

.status-badge.badge-in_progress {
    background: #FFC107;
    color: var(--ink);
    border-color: #e6ad00;
    box-shadow: 0 0 15px rgba(255, 193, 7, 0.3);
}

.status-badge.badge-completed {
    background: #28A745;
    color: var(--white);
    border-color: #28A745;
    box-shadow: 0 0 15px rgba(40, 167, 69, 0.3);
}

.not_started { background: var(--red); }
.in_progress { background: #FFC107; }
.completed { background: #28A745; }

.task-actions {
    width: 240px;
    flex-shrink: 0;
}

/* ===== ANALYTICS LINK ===== */
.container > a {
    display: inline-block;
    margin-bottom: 25px;
    background: var(--blue);
    color: white;
    padding: 12px 24px;
    text-decoration: none;
    font-weight: 700;
    letter-spacing: 1px;
    text-transform: uppercase;
    font-size: 0.9rem;
    transition: all 0.3s var(--ease);
}

.container > a:hover {
    background: var(--ink);
    transform: translateY(-2px);
    box-shadow: 4px 4px 0px var(--blue);
}

/* ===== DELETE LINK ===== */
a[onclick] {
    color: var(--red);
    font-weight: 700;
    text-decoration: none;
    letter-spacing: 0.5px;
    transition: color 0.2s;
}

a[onclick]:hover {
    color: var(--ink);
}

/* ===== GEOMETRIC DECOR ===== */
body::before {
    content: '';
    position: fixed;
    width: 400px; height: 400px;
    background: var(--red);
    border-radius: 50%;
    top: -120px; right: -120px;
    opacity: 0.06;
    pointer-events: none;
    z-index: -1;
    animation: drift 22s infinite alternate ease-in-out;
}

body::after {
    content: '';
    position: fixed;
    width: 300px; height: 300px;
    background: var(--blue);
    bottom: -80px; left: -80px;
    opacity: 0.06;
    pointer-events: none;
    z-index: -1;
    animation: drift 18s infinite alternate-reverse ease-in-out;
}

/* ===== ANIMATIONS ===== */
@keyframes fadeDown {
    from { opacity: 0; transform: translateY(-20px); }
    to { opacity: 1; transform: translateY(0); }
}
@keyframes drift {
    0% { transform: translate(0, 0) rotate(0deg); }
    100% { transform: translate(35px, 45px) rotate(10deg); }
}
@keyframes pulse {
    0%, 100% { transform: scale(1); opacity: 1; }
    50% { transform: scale(1.4); opacity: 0.6; }
}

/* ===== RESPONSIVE ===== */
@media (max-width: 768px) {
    .top { padding: 0 20px; font-size: 0.9rem; }
    .container { width: 95%; margin: 30px auto; }
    .task { flex-direction: column; gap: 15px; }
    .task-actions { width: 100%; }
}
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

<script>
// Staggered reveal for cards
const cards = document.querySelectorAll('.card');
cards.forEach((c, i) => {
    c.style.opacity = '0';
    c.style.transform = 'translateY(25px)';
    c.style.transition = 'all 0.5s cubic-bezier(0.23, 1, 0.32, 1)';
    setTimeout(() => {
        c.style.opacity = '1';
        c.style.transform = 'translateY(0)';
    }, 200 + i * 180);
});

// Convert status text into bold badges
document.querySelectorAll('.status-light').forEach(dot => {
    const statusClass = dot.classList[1]; // not_started, in_progress, completed
    const textNode = dot.nextSibling;
    if (textNode && textNode.nodeType === 3 && textNode.textContent.trim()) {
        const badge = document.createElement('span');
        badge.className = 'status-badge badge-' + statusClass;
        badge.innerHTML = '<span class="status-light ' + statusClass + '" style="width:10px;height:10px;display:inline-block;border-radius:50%;margin-right:6px;vertical-align:middle;"></span>' + textNode.textContent.trim();
        textNode.replaceWith(badge);
        dot.style.display = 'none';
    }
});
</script>

</body>
</html>
