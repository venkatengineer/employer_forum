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
    <title>Dashboard</title>

    <style>
    /* ===== BAUHAUS COLOR SYSTEM ===== */
    :root{
        --blue:#0047FF;
        --red:#FF2B2B;
        --cream:#f7f6f2;
        --ink:#111111;
    }

    /* PAGE BASE */
    body{
        margin:0;
        font-family: Helvetica, Arial, sans-serif;
        background:var(--cream);
        color:var(--ink);
    }

    /* ===== HEADER COMPOSITION ===== */
    .topbar{
        display:flex;
        align-items:center;
        justify-content:space-between;
        background:var(--blue);
        color:white;
        padding:22px 40px;
        letter-spacing:3px;
        font-size:20px;
    }

    /* Red block like Bauhaus poster */
    .brand-accent{
        width:60px;
        height:60px;
        background:var(--red);
    }

    /* Sub grid line */
    .accent-line{
        height:5px;
        background:var(--red);
    }

    /* CONTENT GRID */
    .wrapper{
        width:78%;
        margin:60px auto;
    }

    /* ===== EMPLOYER BLOCK ===== */
    .section{
        margin-bottom:45px;
        border:4px solid var(--blue);
        background:white;
    }

    /* EMPLOYER HEADER */
    .header{
        background:var(--blue);
        color:white;
        padding:16px 24px;
        font-size:18px;
        letter-spacing:2px;
    }

    /* EMPLOYEE ROW */
    .row{
        padding:18px 24px;
        border-top:2px solid #e6e6e6;
        transition:all .18s ease;
    }

    /* Bauhaus interaction = left red bar */
    .row:hover{
        background:#fafafa;
        box-shadow: inset 6px 0 0 var(--red);
    }

    /* LINKS */
    a{
        text-decoration:none;
        color:var(--blue);
        font-weight:600;
    }

    /* ===== GEOMETRIC DECOR ===== */
    .circle{
        position:fixed;
        width:140px;
        height:140px;
        background:var(--red);
        border-radius:50%;
        top:90px;
        right:80px;
        opacity:.9;
    }

    .square{
        position:fixed;
        width:110px;
        height:110px;
        background:var(--blue);
        bottom:80px;
        left:90px;
        opacity:.9;
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



    </body>
    </html>
