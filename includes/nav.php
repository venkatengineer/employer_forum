<?php
/* Start session only if not already started */
if(session_status() === PHP_SESSION_NONE){
    session_start();
}
?>
<style>
/* ===== GLOBAL NAVBAR ===== */
.global-nav{
    display:flex;
    justify-content:space-between;
    align-items:center;
    background:#111;
    color:white;
    padding:12px 30px;
    font-family:Helvetica, Arial, sans-serif;
    letter-spacing:1px;
}

/* LEFT LINKS */
.global-nav .nav-left a{
    color:white;
    text-decoration:none;
    margin-right:18px;
    padding:6px 10px;
    border:2px solid transparent;
    transition:.2s;
}

/* Bauhaus hover accent */
.global-nav .nav-left a:hover{
    border-color:#FF2B2B;
}

/* RIGHT SIDE */
.global-nav .nav-right{
    display:flex;
    align-items:center;
    gap:15px;
    font-size:14px;
}

/* Logout button */
.global-nav .logout{
    background:#FF2B2B;
    color:white;
    text-decoration:none;
    padding:6px 12px;
}

/* Keeps layout consistent */
.page-spacer{
    height:10px;
}
</style>

<div class="global-nav">

    <div class="nav-left">
        <a href="/employee_forums/dashboard.php">HOME</a>
        <a href="javascript:history.back()">BACK</a>
    </div>

    <div class="nav-right">
        <?php if(isset($_SESSION['name'])){ ?>
            <span><?php echo htmlspecialchars($_SESSION['name']); ?></span>
            <a class="logout" href="/employee_forums/logout.php">LOGOUT</a>
        <?php } ?>
    </div>

</div>

<div class="page-spacer"></div>