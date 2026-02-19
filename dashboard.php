    <?php
    session_start();
    include "config.php";
    include "includes/nav.php";
    /* Login required */
    if(!isset($_SESSION['user_id'])){
        header("Location: login.php");
        exit;
    }

    $user_id = $_SESSION['user_id'];
    $role    = $_SESSION['role'];

    /* ADMIN → see all employers */
    if($role == 'admin'){
        $employers = $conn->query("SELECT * FROM users WHERE role='employer'");
    }

    /* EMPLOYER → see only himself */
/* ADMIN + EMPLOYER → see all employers */
if($role == 'admin' || $role == 'employer'){
    $employers = $conn->query("SELECT * FROM users WHERE role='employer' ORDER BY name");
}


    /* EMPLOYEE → skip employer list */
    ?>

    <!DOCTYPE html>
    <html>
    <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;700;900&display=swap" rel="stylesheet">

    <style>
    /* ===== BAUHAUS DESIGN SYSTEM ===== */
    :root {
        --blue: #0047FF;
        --blue-dark: #0033CC;
        --blue-light: #3370FF;
        --red: #EE2A1B;
        --cream: #F7F6F2;
        --ink: #121212;
        --white: #FFFFFF;
        --ease: cubic-bezier(0.23, 1, 0.32, 1);
    }

    * { box-sizing: border-box; }

    /* PAGE BASE */
    body {
        margin: 0;
        font-family: 'Outfit', Helvetica, Arial, sans-serif;
        background: var(--cream);
        color: var(--ink);
        overflow-x: hidden;
    }

    /* ===== HEADER COMPOSITION ===== */
    .topbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: var(--blue);
        color: white;
        padding: 0 40px;
        height: 75px;
        letter-spacing: 4px;
        font-size: 1.2rem;
        font-weight: 900;
        text-transform: uppercase;
        position: relative;
        overflow: hidden;
        animation: fadeDown 0.6s var(--ease) both;
    }

    .topbar::after {
        content: '';
        position: absolute;
        right: 0; top: 0;
        width: 80px; height: 100%;
        background: var(--red);
        clip-path: polygon(30% 0, 100% 0, 100% 100%, 0% 100%);
    }

    /* Red block like Bauhaus poster */
    .brand-accent {
        width: 50px;
        height: 50px;
        background: var(--red);
        z-index: 1;
        animation: spinIn 0.8s var(--ease) forwards 0.3s;
        opacity: 0;
        transform: rotate(-90deg) scale(0.5);
    }

    /* Sub grid line */
    .accent-line {
        height: 6px;
        background: linear-gradient(90deg, var(--red) 0%, var(--red) 30%, var(--blue-dark) 30%, var(--blue-dark) 100%);
        opacity: 0;
        animation: fadeIn 0.5s forwards 0.3s;
    }

    /* CONTENT GRID */
    .wrapper {
        max-width: 1100px;
        width: 90%;
        margin: 50px auto;
        padding-bottom: 60px;
    }

    /* ===== EMPLOYER BLOCK ===== */
    .section {
        margin-bottom: 30px;
        border: 4px solid var(--ink);
        background: white;
        transition: all 0.45s var(--ease);
    }

    .section.animate-hidden {
        opacity: 0;
        transform: translateY(25px);
    }

    .section.animate-visible {
        opacity: 1;
        transform: translateY(0);
    }

    .section:hover {
        box-shadow: 10px 10px 0px var(--blue);
    }

    /* EMPLOYER HEADER */
    .header {
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

    .header::before {
        content: '';
        width: 14px; height: 14px;
        background: var(--red);
        border-radius: 50%;
        flex-shrink: 0;
        animation: pulse 2s infinite;
    }

    /* EMPLOYEE ROW */
    .row {
        padding: 18px 28px;
        border-top: 2px solid #eee;
        transition: all 0.35s var(--ease);
        position: relative;
    }

    .row::before {
        content: '';
        position: absolute;
        left: 0; top: 0;
        width: 0; height: 100%;
        background: var(--red);
        transition: width 0.3s ease;
    }

    /* Bauhaus interaction = left red bar + slide */
    .row:hover {
        background: #f5f5ff;
        padding-left: 38px;
    }

    .row:hover::before {
        width: 6px;
    }

    /* LINKS */
    a {
        text-decoration: none;
        color: var(--ink);
        font-weight: 600;
        letter-spacing: 0.5px;
        transition: color 0.2s;
    }

    a:hover {
        color: var(--blue);
    }

    /* ===== GEOMETRIC DECOR ===== */
    .circle {
        position: fixed;
        width: 400px;
        height: 400px;
        background: var(--red);
        border-radius: 50%;
        top: -120px;
        right: -120px;
        opacity: 0.06;
        pointer-events: none;
        animation: drift 22s infinite alternate ease-in-out;
    }

    .square {
        position: fixed;
        width: 300px;
        height: 300px;
        background: var(--blue);
        bottom: -80px;
        left: -80px;
        opacity: 0.06;
        pointer-events: none;
        animation: drift 18s infinite alternate-reverse ease-in-out;
    }

    /* ===== ANIMATIONS ===== */
    @keyframes fadeDown {
        from { opacity: 0; transform: translateY(-20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    @keyframes fadeIn {
        to { opacity: 1; }
    }
    @keyframes spinIn {
        to { opacity: 1; transform: rotate(0deg) scale(1); }
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
        .topbar { padding: 0 20px; font-size: 0.9rem; letter-spacing: 2px; }
        .wrapper { width: 95%; margin: 30px auto; }
        .action-link { font-size: 0.8rem; padding: 10px 18px; }
    }

    /* ===== ACTION LINKS ===== */
    .action-link {
        display: inline-block;
        margin-bottom: 30px;
        background: var(--blue);
        color: var(--white, #FFFFFF);
        padding: 14px 28px;
        text-decoration: none;
        font-weight: 700;
        font-size: 0.9rem;
        letter-spacing: 2px;
        text-transform: uppercase;
        border: 3px solid var(--ink);
        transition: all 0.3s cubic-bezier(0.23, 1, 0.32, 1);
    }

    .action-link:hover {
        background: var(--ink);
        color: #FFFFFF;
        transform: translateY(-3px);
        box-shadow: 6px 6px 0px var(--blue);
    }
    </style>

    </head>

    <body>

    <div class="topbar">
        <div>EMPLOYEE WORK FORUM</div>
        <div class="brand-accent"></div>
    </div>
    <div class="accent-line"></div>

    <div class="circle"></div>
    <div class="square"></div>

    <div class="wrapper">

    <?php if($role == 'employer'){ ?>
    <a href="workload.php" class="action-link">📊 View Team Workload</a>
    <?php } ?>

    <?php if($role != 'employee'){ ?>

    <?php while($emp = $employers->fetch_assoc()){ ?>

    <div class="section">
    <div class="header"><?php echo $emp['name']; ?> (Employer)</div>

    <?php
    /* Load employees under this employer */
    $employees = $conn->query("SELECT id,name FROM users WHERE employer_id=".$emp['id']." ORDER BY name");

    while($e = $employees->fetch_assoc()){
    ?>

    <div class="row">

    <?php if($role == 'admin' || $role == 'employer'){ ?>
        <!-- Employer/Admin can open employee -->
        <a href="view_tasks.php?id=<?php echo $e['id']; ?>">
            <?php echo $e['name']; ?>
        </a>
    <?php } else { ?>
        <?php echo $e['name']; ?>
    <?php } ?>

    </div>

    <?php } ?>
    </div>

    <?php } ?>

    <?php } ?>

    <?php
    /* EMPLOYEE → only see himself */
    if($role == 'employee'){
    ?>
    <div class="section">
    <div class="header">My Profile</div>

    <div class="row">
    <a href="view_tasks.php?id=<?php echo $user_id; ?>">
    View My Tasks
    </a>
    </div>

    </div>
    <?php } ?>
    </div> <!-- wrapper -->


    <script>
    // Staggered reveal animation for .section blocks
    // First hide them via JS (so they're visible if JS fails)
    const sections = document.querySelectorAll('.section');
    sections.forEach(s => s.classList.add('animate-hidden'));

    // Then reveal them with stagger
    sections.forEach((s, i) => {
        setTimeout(() => {
            s.classList.remove('animate-hidden');
            s.classList.add('animate-visible');
        }, 300 + i * 150);
    });
    </script>

    </body>
    </html>
