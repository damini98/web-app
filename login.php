<?php // Do not put any HTML above this line

if (isset($_POST['cancel'])) {
    // Redirect the browser to game.php
    header("Location: index.php");
    return;
}
//require_once "head.php";
require_once "pdo.php";
require_once "util.php";
session_start();

$salt = 'XyZzy12*_';
//$stored_hash = 'php123';  // Pw is php123

// Check to see if we have some POST data, if we do process it

if (isset($_POST['email']) && isset($_POST['pass'])) {
    if (strlen($_POST['email']) < 1 || strlen($_POST['pass']) < 1) {
        $_SESSION['error'] = "Email and password are required";
        header("Location: login.php");
        return;
    } else {
        $check = hash('md5', $salt.$_POST['pass']);
        // Redirect the browser to game.php
        $stmt = $pdo->prepare('SELECT user_id, name FROM users WHERE email = :em AND password = :pw');
        $stmt->execute(array(':em' => $_POST['email'], ':pw' => $check));
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row !== false) {
            $_SESSION['name'] = $_POST['email'];
            $_SESSION['user_id'] = $row['user_id'];
            error_log("Login success " . $_POST['email']);
            $_SESSION['success'] = "Log in success.";
            header("Location: index.php");
            return;
        } else {
            $_SESSION['error'] = "Incorrect Password.";
            header("Location: login.php");
            error_log("Login fail " . $_POST['email'] . " $check");
            return;
        }
    }
}

?>
<!DOCTYPE html>
<html>

<head>
    <title>Damini Sheth Login Page</title>
</head>

<body>
    <div class="container">
        <h1>Please Log In</h1>
        <?php
        flashMsg();
        ?>

        <form method="POST" action="login.php">
            <label for="email">Email</label>
            <input type="text" name="email" id="email"><br />
            <label for="id_1723">Password</label>
            <input type="text" name="pass" id="id_1723"><br />
            <input type="submit" onclick="return doValidate();" value="Log In">
            <a href="index.php" name='cancel'> Cancel </a>
        </form>

        <script>
            function doValidate() {
                console.log('Validating...');
                try {
                    addr = document.getElementById('email').value;
                    pw = document.getElementById('id_1723').value;
                    console.log("Validating addr=" + addr + " pw=" + pw);
                    if (addr == null || addr == "" || pw == null || pw == "") {
                        alert("Both fields must be filled out");
                        return false;
                    }
                    if (addr.indexOf('@') == -1) {
                        alert("Invalid Email Address. Need @");
                        return false;
                    }
                    return true;
                } catch (e) {
                    return false;
                }
                return false;
            }
        </script>
    </div>
</body>

</html>