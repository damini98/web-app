
<!DOCTYPE html>
<html>
<head>
<title>Damini Sheth </title>
</head>
<body>
<div class="container">
<h1>Welcome to Damini Sheth's Resume Registry</h1>

</div>
<?php
require_once "head.php";
require_once "pdo.php";
require_once "util.php";
session_start();
flashMsg();
?>
<table border="1">
    <?php
        $stm = $pdo->query("SELECT * FROM profile");
        echo nl2br ("\n");
        echo ("<tr><td>");
        echo("Name");
        echo ("</td><td>");
        echo("Headline");
        if(isset($_SESSION['user_id'])) {
            echo ("</td><td>");
            echo("Action");
        }
        echo ("</td></tr\n");
        
        while ($row = $stm->fetch(PDO::FETCH_ASSOC)){
            echo ("<tr><td>");

            $_SESSION['profile_id'] = $row['profile_id'];
            $x= $row["profile_id"];
            
            $link_address = 'view.php?profile_id='.$x; 
            echo ("<a href='$link_address'> " . htmlentities($row['first_name']) ."</a>");

            
            echo ("</td><td>");
            echo htmlentities(($row['headline']));
            
            if(isset($_SESSION['user_id'])) {
                echo ("</td><td>");
                $link_address1 = 'edit.php?profile_id='.$x; 
                $link_address2 = 'delete.php?profile_id='.$x; 
                echo ("<a href='$link_address1'> Edit    </a>");
                echo nl2br ("\n");
                echo ("<a href='$link_address2'>    Delete </a>");
            }
            echo ("</td></tr\n");
            
            
        }
    ?>

</table>


<?php
if ($stm == false){
    echo("No rows found");
}

if (isset($_SESSION['user_id']) ) {
    echo('<a href="add.php"> Add New Entry </a>');
    echo nl2br ("\n");
    echo nl2br ("\n");
    echo('<a href="logout.php"> Log Out </a>');
    echo nl2br ("\n");
    echo nl2br ("\n");
}
else{
    echo('<a href="login.php"> Please log in </a>');
    echo nl2br ("\n");
    echo nl2br ("\n");
}


?>


</body>
</html>