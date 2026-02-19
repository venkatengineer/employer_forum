<?php
include "config.php";

/* Check if connection exists */
if ($conn) {

    /* Try a simple query to confirm DB access */
    $testQuery = "SELECT 1";
    $result = $conn->query($testQuery);

    if ($result) {
        echo "<h2 style='color:green;'>✅ Database Connected Successfully!</h2>";
        echo "<p>Connection to <b>employee_forum</b> is working.</p>";
    } else {
        echo "<h2 style='color:red;'>❌ Connected but Database Not Accessible.</h2>";
    }

} else {
    echo "<h2 style='color:red;'>❌ Connection Failed.</h2>";
}
?>
