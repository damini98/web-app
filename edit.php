<?php
require_once "head.php";
require_once "pdo.php";
require_once "util.php";

session_start();

if ( ! isset($_SESSION['user_id']) ) {
    die('ACCESS DENIED');
}

if (isset($_POST['update'])){
    if (isset($_POST['first_name']) &&  strlen($_POST['first_name']) > 0 
    && strlen($_POST['email']) > 0 && strlen($_POST['last_name']) > 0
    && strlen($_POST['headline']) > 0 && strlen($_POST['summary']) > 0){
        if( strpos($_POST['email'], '@') !== false){
            
            $msg = validatePos();

            if (is_string($msg)) {
                $_SESSION['error'] = $msg;
                header("Location: edit.php?profile_id=".$_REQUEST['profile_id']);
                return;
            }


            $stm = $pdo->prepare("UPDATE profile SET first_name = :fn, last_name = :ln, 
            email = :em, headline = :he, summary = :su
             WHERE profile_id = :pid AND user_id = :uid");
            

            $stm->execute(array(
            ':uid' => $_SESSION['user_id'],
            ':fn' => $_POST['first_name'],
            ':ln' => $_POST['last_name'],
            ':em' => $_POST['email'],
            ':he' => $_POST['headline'],
            ':su' => $_POST['summary'],
            ':pid' => $_REQUEST['profile_id'])
            );
            $stmt = $pdo->prepare('DELETE FROM Position WHERE profile_id=:pid');
            $stmt->execute(array( ':pid' => $_REQUEST['profile_id']));

            
            $rank = 1;
            for($i=1; $i<=9; $i++) {
                if ( ! isset($_POST['year'.$i]) ) continue;
                if ( ! isset($_POST['desc'.$i]) ) continue;

                $year = $_POST['year'.$i];
                $desc = $_POST['desc'.$i];
                $stmt = $pdo->prepare('INSERT INTO Position
                    (profile_id, rank, year, description)
                    VALUES ( :pid, :rank, :year, :desc)');

                $stmt->execute(array(
                ':pid' => $_REQUEST['profile_id'],
                ':rank' => $rank,
                ':year' => $year,
                ':desc' => $desc)
                );
                $rank++;
            }
            $stmt = $pdo->prepare('DELETE FROM education WHERE profile_id=:pid');
            $stmt->execute(array( ':pid' => $_REQUEST['profile_id']));

            //TO DO: this
            $rank = 1;
            for($i=1; $i<=9; $i++) {
                if ( ! isset($_POST['year'.$i]) ) continue;
                if ( ! isset($_POST['school'.$i]) ) continue;

                $year = $_POST['year'.$i];
                $school = $_POST['school'.$i];

                $institution_id = false;

                $stmt = $pdo->prepare('SELECT institution_id from institution where name = :name');
                $stmt->execute(array('name' => $school));
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                if ( $row !== false ) {
                    $institution_id = $row['institution_id'];
                }
                else{
                    $stmt = $pdo->prepare('INSERT INTO institution (name) values (:name)');
                    $stmt->execute(array('name' => $school));
                    $institution_id = $pdo->lastInsertId();
                }

                $stmt = $pdo->prepare('INSERT INTO education (profile_id, rank, year, institution_id) VALUES ( :pid, :erank, :eyear, :inst)');
                $stmt->execute(array(
                    ':pid' => $_REQUEST['profile_id'],
                    ':erank' => $rank,
                    ':eyear' => $year,
                    ':inst' => $institution_id)
                    );

                $rank++;
            }



            $_SESSION['success'] = "Profile Updated.";
            header("Location: index.php");
            return;
        }
        else{
            $_SESSION['error'] = "Email address must contain @";
            header("Location: edit.php?profile_id=".$_REQUEST['profile_id']);
            return;
        }
    }
    else{
        $_SESSION['error'] = "All fields are required";
        header("Location: edit.php?profile_id=".$_REQUEST['profile_id']);
        return;
    }
}


$stmt = $pdo->prepare("SELECT * FROM profile where profile_id = :xyz AND user_id = :uid");
$stmt->execute(array(":xyz" => $_REQUEST['profile_id'], ':uid' => $_SESSION['user_id']));
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if ( $row === false ) {
    $_SESSION['error'] = 'Could not load profile';
    header( 'Location: index.php' ) ;
    return;
}

$fn = htmlentities($row['first_name']);
$ln = htmlentities($row['last_name']);
$eid = htmlentities($row['email']);
$hd = htmlentities($row['headline']);
$sm = htmlentities($row['summary']);


$profile_id = $row['profile_id'];

flashMsg();
//unset($_SESSION['id']);
//TODO: pos
$positions = loadPos($pdo, $_REQUEST['profile_id']);
$schools = loadEdu($pdo, $_REQUEST['profile_id']);

?>
<html>
<title> Damini Sheth </title>
<body>
<p>Edit Profile for <?php echo ($fn);?> </p>
<form method="post">
<p>First Name:
<input type="text" name="first_name" value="<?= $fn ?>"></p>
<p>Last Name:
<input type="text" name="last_name" value="<?= $ln ?>"></p>
<p>Email:
<input type="text" name="email" value="<?= $eid ?>"></p>
<p>Headline:
<input type="text" name="headline" value="<?= $hd ?>"></p>
<p>Summary:
<input type="text" name="summary" value="<?= $sm ?>"></p>

<?php
$pos = 0;

echo('<p>Position: <input type="submit" id="addPos" value="+">'."\n");
echo('<div id="position_fields">'."\n");
foreach ($positions as $position){
    $pos++;
    echo('<div id="position'.$pos.'">'."\n");
    echo('<p>Year: <input type="text" name="year'.$pos.'"');
    echo('value="'.$position['year'].'"/>'."\n");
    echo('<input type="button" value="-"');
    echo('onclick="$(\'#position'.$pos.'\').remove();return false;">'."\n");
    echo('</p>');
    echo('<textarea name="desc'.$pos.'" rows="8" cols="80">'."\n");
    echo(htmlentities($position['description'])."\n");
    echo("\n</textarea>\n</div>\n");

}
echo("</div></p>\n");

$edu = 0;

echo('<p>Education: <input type="submit" id="addEdu" value="+">'."\n");
echo('<div id="edu_fields">'."\n");

if (count($schools) > 0){
    foreach ($schools as $school){
        $edu++;
        echo('<div id="edu'.$edu.'">'."\n");
        echo('<p>Year: <input type="text" name="year'.$edu.'"');
        echo('value="'.$school['year'].'"/>'."\n");
        echo('<input type="button" value="-"');
        echo('onclick="$(\'#edu'.$edu.'\').remove();return false;">'."\n");
        echo('</p>');
        echo('<p>School: <input type="text" name="school'.$edu.'" size="80" class="school" value="'.htmlentities($school['name']).'"/>'."\n");
        echo('</p>');
        echo("\n</div>\n");
    }
}
echo("</div></p>\n");

?>


<input type="hidden" name="profile_id" value="<?= $profile_id ?>">
<p><input type="submit" name="update" value="Save"/>
<a href="index.php">Cancel</a></p>
</form>

<script>
    
countPos = <?= $pos ?>;
countEdu = <?= $edu ?>;
$(document).ready(function() {
    window.console && console.log('Document ready called');
    $('#addPos').click(function(event) {
        // http://api.jquery.com/event.preventdefault/
        event.preventDefault();
        if (countPos >= 9) {
            alert("Maximum of nine position entries exceeded");
            return;
        }
        countPos++;
        window.console && console.log("Adding position " + countPos);
        $('#position_fields').append(
            '<div id="position' + countPos + '"> \
        <p>Year: <input type="text" name="year' + countPos + '" value="" /> \
        <input type="button" value="-" \
            onclick="$(\'#position' + countPos + '\').remove();return false;"></p> \
        <textarea name="desc' + countPos + '" rows="8" cols="80"></textarea>\
        </div>');
    });
    $('#addEdu').click(function(event) {
        // http://api.jquery.com/event.preventdefault/
        event.preventDefault();
        if (countEdu >= 9) {
            alert("Maximum of nine education entries exceeded");
            return;
        }
        countEdu++;
        window.console && console.log("Adding education " + countEdu);
        var source  = $("#edu-template").html();
        $('#edu_fields').append(source.replace(/@COUNT@/g,countEdu));

        // Add the even handler to the new ones
        $('.school').autocomplete({
            source: "school.php"
        });

    });

    $('.school').autocomplete({
        source: "school.php"
    });
    //     $('#edu_fields').append(
    //         '<div id="school' + countEdu + '"> \
    //     <p>Year: <input type="text" name="year' + countEdu + '" value="" /> \
    //     <input type="button" value="-" \
    //         onclick="$(\'#school' + countEdu + '\').remove();return false;"></p> \
    //     <p> School: <input type="text" name="school' + countEdu+ '" size="80" /> </p>\
    //     </div>');

    //     $('.school').autocomplete({
    //         source: "school.php"
    //     });
    // });
    // $('.school').autocomplete({
    //     source: "school.php"
    // });

});
</script>
<script id="edu-template" type="text">

  <div id="edu@COUNT@">
    <p>Year: <input type="text" name="year@COUNT@" value="" />
    <input type="button" value="-" onclick="$('#edu@COUNT@').remove();return false;"><br>
    <p>School: <input type="text" size="80" name="school@COUNT@" class="school" value="" />
    </p>
  </div>
</script>
</body>
</html>
