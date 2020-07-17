<?php
require_once "head.php";
require_once "pdo.php";
require_once "util.php";

session_start();

if (!isset($_SESSION['user_id'])) {
    die('ACCESS DENIED');
}

if (isset($_POST['cancel'])) {
    $_SESSION['cancel'] = $_POST['cancel'];
    header('Location: index.php');
    unset($_SESSION['cancel']);
}

if (isset($_POST['add'])) {
    
    if (
        isset($_POST['first_name']) &&  strlen($_POST['first_name']) > 0
        && strlen($_POST['email']) > 0 && strlen($_POST['last_name']) > 0
        && strlen($_POST['headline']) > 0 && strlen($_POST['summary']) > 0
    ) {
        if (strpos($_POST['email'], '@') !== false) {

            $stm = $pdo->prepare('INSERT INTO profile(user_id, first_name, last_name, email, headline, summary) 
            VALUES ( :uid, :fn, :ln, :em, :he, :su)');

            $stm->execute(array(
                ':uid' => $_SESSION['user_id'],
                ':fn' => $_POST['first_name'],
                ':ln' => $_POST['last_name'],
                ':em' => $_POST['email'],
                ':he' => $_POST['headline'],
                ':su' => $_POST['summary']
            ));

            $msg = validatePos();

            if (is_string($msg)) {
                $_SESSION['error'] = $msg;
                header("Location: add.php");
                return;
            }

            $profile_id = $pdo->lastInsertId();
            $rank = 1;
            for($i=1; $i<=9; $i++) {
                if ( ! isset($_POST['year'.$i]) ) continue;
                if ( ! isset($_POST['desc'.$i]) ) continue;
            
                $year = $_POST['year'.$i];
                $desc = $_POST['desc'.$i];

                $stmt = $pdo->prepare('INSERT INTO position (profile_id, rank, year, description) VALUES ( :pid, :rank, :year, :desc)');

                $stmt->execute(array(
                    ':pid' => $profile_id,
                    ':rank' => $rank,
                    ':year' => $year,
                    ':desc' => $desc
                ));

                $rank++;
            }

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
                    ':pid' => $profile_id,
                    ':erank' => $rank,
                    ':eyear' => $year,
                    ':inst' => $institution_id)
                    );

                $rank++;
            }




            $_SESSION['add'] = $_POST['add'];
            $_SESSION['success'] = "Profile added.";
            header('Location: index.php');
            return;
        } else {
            $_SESSION['error'] = "Email address must contain @";
            header("Location: add.php");
            return;
        }
    } else {
        $_SESSION['error'] = "All fields are required";
        header("Location: add.php");
        return;
    }
    //unset($_SESSION['add']);
}


?>
<html>
<h1> Adding Profile for <?php echo $_SESSION['name']; ?> </h1>
<?php

flashMsg();

?>
<form method="post">
    <p>First Name:
        <input type="text" name="first_name" size="60" /></p>
    <p>Last Name:
        <input type="text" name="last_name" size="60" /></p>
    <p>Email:
        <input type="text" name="email" size="30" /></p>
    <p>Headline:<br />
        <input type="text" name="headline" size="80" /></p>
    <p>Summary:<br />
        <textarea name="summary" rows="8" cols="80"></textarea>
    <p> Position: <input type="submit" id="addPos" value="+"> <div id="position_fields"></div>
    </p>
    <p> Education: <input type="submit" id="addEdu" value="+"> <div id="edu_fields"></div>
    </p>

    
        <p> <input type="submit" name="add" value="Add New"> </p>
        <p> <input type="submit" name="cancel" value="Cancel"> </p>
    </p>
</form>

<script>
    countPos = 0;
    countEdu = 0;
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
</html>