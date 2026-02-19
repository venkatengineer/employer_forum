<?php
session_start();
include "config.php";
include "includes/nav.php";

if($_SESSION['role'] != 'employer'){
    die("Only employers can view workload");
}

$employer_id = $_SESSION['user_id'];

/* Capacity rule */
$capacity = 5;

/* Get employees under this employer */
$employees = $conn->query(
"SELECT id,name FROM users WHERE employer_id=$employer_id"
);
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Workload Meter</title>
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

/* ===== TOP HEADER (h2) ===== */
h2 {
    margin: 0;
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

h2::after {
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

/* ===== EMPLOYEE CARD ===== */
.employee {
    background: white;
    border: 4px solid var(--ink);
    margin-bottom: 24px;
    padding: 28px;
    transition: all 0.45s var(--ease);
}

.employee:hover {
    box-shadow: 10px 10px 0px var(--blue);
}

/* NAME */
.name {
    font-size: 1.1rem;
    font-weight: 900;
    letter-spacing: 1px;
    text-transform: uppercase;
    margin-bottom: 16px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.name::before {
    content: '';
    width: 12px; height: 12px;
    background: var(--blue);
    border-radius: 50%;
    flex-shrink: 0;
    animation: pulse 2s infinite;
}

/* BAR BACK */
.bar {
    height: 32px;
    background: #e8e8e8;
    position: relative;
    border: 3px solid var(--ink);
    overflow: hidden;
}

/* BAR FILL */
.fill {
    height: 100%;
    width: 0%;
    transition: width 1.5s var(--ease);
    position: relative;
}

.fill::after {
    content: '';
    position: absolute;
    right: 0; top: 0;
    width: 4px; height: 100%;
    background: rgba(255,255,255,0.5);
    animation: shimmer 1.5s infinite;
}

/* COLORS */
.low {
    background: linear-gradient(90deg, #28A745, #34d058);
    box-shadow: inset 0 0 10px rgba(40, 167, 69, 0.3);
}
.medium {
    background: linear-gradient(90deg, #e6ad00, #FFC107);
    box-shadow: inset 0 0 10px rgba(255, 193, 7, 0.3);
}
.high {
    background: linear-gradient(90deg, #cc2216, var(--red));
    box-shadow: inset 0 0 10px rgba(238, 42, 27, 0.3);
}

/* PERCENT INFO */
.percent {
    margin-top: 12px;
    font-size: 0.85rem;
    font-weight: 600;
    letter-spacing: 0.5px;
    display: flex;
    align-items: center;
    gap: 12px;
}

.capacity-tag {
    display: inline-block;
    padding: 3px 12px;
    font-size: 0.7rem;
    font-weight: 900;
    letter-spacing: 1px;
    text-transform: uppercase;
    border: 2px solid;
}

.tag-low {
    color: #28A745;
    border-color: #28A745;
    background: rgba(40, 167, 69, 0.08);
}
.tag-medium {
    color: #e6ad00;
    border-color: #e6ad00;
    background: rgba(255, 193, 7, 0.08);
}
.tag-high {
    color: var(--red);
    border-color: var(--red);
    background: rgba(238, 42, 27, 0.08);
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
@keyframes shimmer {
    0%, 100% { opacity: 0.5; }
    50% { opacity: 1; }
}

@media (max-width: 768px) {
    h2 { padding: 0 20px; font-size: 0.9rem; }
    .container { width: 95%; margin: 30px auto; }
}
</style>
</head>

<body>

<div class="container">
<h2>Team Workload Distribution</h2>

<?php while($emp = $employees->fetch_assoc()){

    /* Count active tasks */
    $active = $conn->query(
    "SELECT COUNT(*) c FROM tasks
     WHERE assigned_to=".$emp['id']." AND status!='completed'"
    )->fetch_assoc()['c'];

    $workload = min(100, round(($active/$capacity)*100));

    /* Decide color */
    if($workload < 40) $class="low";
    elseif($workload < 75) $class="medium";
    else $class="high";
?>

<div class="employee">
<div class="name"><?php echo $emp['name']; ?></div>

<div class="bar">
<div class="fill <?php echo $class; ?>" data-width="<?php echo $workload; ?>"></div>
</div>

<div class="percent">
<?php echo $active; ?> Active Tasks • <?php echo $workload; ?>% Capacity Used
</div>
</div>

<?php } ?>

</div>

<script>
/* Animate bars with stagger */
const empCards = document.querySelectorAll('.employee');
empCards.forEach((card, i) => {
    card.style.opacity = '0';
    card.style.transform = 'translateY(25px)';
    card.style.transition = 'all 0.5s cubic-bezier(0.23, 1, 0.32, 1)';
    setTimeout(() => {
        card.style.opacity = '1';
        card.style.transform = 'translateY(0)';
    }, 300 + i * 150);
});

/* Animate bars */
document.querySelectorAll('.fill').forEach((el, i) => {
    let w = el.dataset.width;
    setTimeout(() => { el.style.width = w + '%'; }, 600 + i * 200);
});

/* Add capacity tags */
document.querySelectorAll('.percent').forEach(el => {
    const text = el.textContent;
    const match = text.match(/(\d+)%/);
    if (match) {
        const pct = parseInt(match[1]);
        let label, cls;
        if (pct < 40) { label = 'Available'; cls = 'tag-low'; }
        else if (pct < 75) { label = 'Moderate'; cls = 'tag-medium'; }
        else { label = 'Overloaded'; cls = 'tag-high'; }
        const tag = document.createElement('span');
        tag.className = 'capacity-tag ' + cls;
        tag.textContent = label;
        el.appendChild(tag);
    }
});
</script>

</body>
</html>
