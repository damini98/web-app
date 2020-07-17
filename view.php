<?php
require_once "head.php";
require_once "pdo.php";
require_once "util.php";
session_start();

?>

<html>
<head></head>
<body>
<h2> Profile information </h2>
    <?php
    //WHERE profile_id = :id "
        $y = ($_GET['profile_id']);
        $stm = $pdo->query("SELECT * FROM profile where profile_id = $y");
        //$stm->execute(array(':pid' => $_GET['profile_id']));
        

        while ($row = $stm->fetch(PDO::FETCH_ASSOC)){
            echo("First Name: ");
            echo (htmlentities($row['first_name']));
            echo nl2br ("\n");
            echo("Last Name: ");
            echo (htmlentities($row['last_name']));
            echo nl2br ("\n");
            echo("Email: ");
            echo (htmlentities($row['email']));
            echo nl2br ("\n");
            echo("Headline: ");
            echo (htmlentities($row['headline']));
            echo nl2br ("\n");
            echo("Summary: ");
            echo (htmlentities($row['summary']));
            echo nl2br ("\n");
            echo("Position: ");
            $positions = loadPos($pdo, $_REQUEST['profile_id']);
            echo('<ul>');
            foreach ($positions as $position){
                echo('<li>');
                echo($position['year']);
                echo(" - ");
                echo($position['description']);
                echo('</li>');
            }
            echo('</ul>');
            echo nl2br ("\n");

            echo("Education: ");
            $edus = loadEdu($pdo, $_REQUEST['profile_id']);
            echo('<ul>');
            foreach ($edus as $edu){
                echo('<li>');
                echo($edu['year']);
                echo(" - ");
                echo($edu['name']);
                echo('</li>');
            }
            echo('</ul>');
            echo nl2br ("\n");
            //TODO: pos
        }
    ?>

</table>


<p>

<a href="index.php">Done</a>
</p>

</body>
</html>
 

