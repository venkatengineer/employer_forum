<?php
session_start();
include "config.php";

$error = "";

if($_SERVER["REQUEST_METHOD"] == "POST"){

    $email    = trim($_POST['email']);
    $password = trim($_POST['password']);

    /* Prepare query (ONLY ONCE) */
    $stmt = $conn->prepare("SELECT id, name, password, role FROM users WHERE email=? LIMIT 1");

    if($stmt){

        $stmt->bind_param("s", $email);
        $stmt->execute();

        /* Required for PHP 5.6 */
        $stmt->store_result();

        if($stmt->num_rows == 1){

            $stmt->bind_result($id, $name, $hashedPassword, $role);
            $stmt->fetch();

            /* Verify hashed password */
            if(password_verify($password, $hashedPassword)){

                $_SESSION['user_id'] = $id;
                $_SESSION['role']    = $role;
                $_SESSION['name']    = $name;

                header("Location: dashboard.php");
                exit;
            } else {
                $error = "Wrong Password!";
            }

        } else {
            $error = "User Not Found!";
        }

        $stmt->close();

    } else {
        $error = "Database Error!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Employee Forum Login</title>

<style>
body{
    margin:0;
    font-family: Helvetica, Arial, sans-serif;
    background:#f4f4f4;
}

.container{
    display:flex;
    height:100vh;
}

/* LEFT PANEL */
.left{
    flex:1;
    background:#0047FF;
    color:white;
    display:flex;
    align-items:center;
    justify-content:center;
    text-align:center;
}

.left h1{
    font-size:60px;
    letter-spacing:6px;
    margin:0;
}

/* RIGHT PANEL */
.right{
    flex:1;
    display:flex;
    align-items:center;
    justify-content:center;
    background:#ffffff;
}

.login-box{
    width:350px;
}

.bar{
    width:80px;
    height:10px;
    background:#FF2B2B;
    margin-bottom:30px;
}

h2{
    margin-bottom:30px;
    letter-spacing:3px;
}

input{
    width:100%;
    padding:14px;
    margin-bottom:20px;
    border:none;
    background:#f0f0f0;
}

button{
    width:100%;
    padding:14px;
    border:none;
    background:#FF2B2B;
    color:white;
    letter-spacing:2px;
    cursor:pointer;
}

button:hover{
    background:#d81f1f;
}

.error{
    color:red;
    margin-bottom:15px;
}

/* Bauhaus Shapes */
.circle{
    position:absolute;
    width:120px;
    height:120px;
    background:#FF2B2B;
    border-radius:50%;
    top:40px;
    left:40px;
}

.square{
    position:absolute;
    width:90px;
    height:90px;
    background:#0047FF;
    bottom:40px;
    right:40px;
}
</style>
</head>

<body>

<div class="circle"></div>
<div class="square"></div>

<div class="container">

    <div class="left">
        <div>
            <h1>FORUM</h1>
            <p>Work Allocation System</p>
        </div>
    </div>

    <div class="right">
        <div class="login-box">

            <div class="bar"></div>
            <h2>LOGIN</h2>

            <?php if($error!=""){ ?>
                <div class="error"><?php echo $error; ?></div>
            <?php } ?>

            <form method="POST">
                <input type="email" name="email" placeholder="EMAIL" required>
                <input type="password" name="password" placeholder="PASSWORD" required>
                <button type="submit">ENTER</button>
            </form>

        </div>
    </div>

</div>

</body>
</html>
