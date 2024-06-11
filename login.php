<?php
require_once 'db.php';

if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true) {
    header("location: dashboard.php");
    exit;
}

$nickname = $password = "";
$nickname_err = $password_err = $login_err = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (empty(trim($_POST["nickname"]))) {
        $nickname_err = "Blogi prisijungimo duomenys";
    } else {
        $nickname = trim($_POST['nickname']);
    }

    if (empty(trim($_POST["password"]))) {
        $password_err = "Blogi prisijungimo duomenys";
    } else {
        $password = trim($_POST['password']);
    }

    if (empty($nickname_err) && empty($password_err)) {
        $sql = "SELECT id, nickname, name, surname, email, password FROM users WHERE nickname = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("s", $param_nick);
            $param_nick = $nickname;

            if ($stmt->execute()) {
                $stmt->store_result();
                if ($stmt->num_rows == 1) {
                    $stmt->bind_result($id, $nickname, $name, $surname, $email, $hashed_password);
                    if ($stmt->fetch()) {
                        if (password_verify($password, $hashed_password)) {
                            session_start();
                            $_SESSION["loggedin"] = true;
                            $_SESSION["id"] = $id;
                            $_SESSION["nickname"] = $nickname;

                            header("location: dashboard.php");
                            exit;
                        } else {
                            $login_err = "Blogi prisijungimo duomenys";
                        }
                    }
                } else {
                    $login_err = "Blogi prisijungimo duomenys";
                }
            } else {
                $login_err = "Įvyko klaida, bandykite dar kartą.";
            }
            $stmt->close();
        }
    }
    $conn->close();
}
?>

<?php include 'header.php'; ?>
<div class="login-container">
    <div class="login-form">
        <h1 class="login_page_title">Prisijungimas</h1>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-control">
                <input type="text" name="nickname" value="<?php echo $nickname; ?>" placeholder="Prisijungimo vardas">
                <span><?php echo $nickname_err; ?></span>
            </div>
            <div class="form-control">
                <input type="password" name="password" value="<?php echo $password; ?>" placeholder="Slaptažodis">
                <span><?php echo $password_err; ?></span>
            </div>
            <div class="buttons button_pos">
                <button type="submit" class="material-button log-btn">Prisijungti</button>
                <a href="register.php" class="material-button reg-btn">Registruotis<i
                        class="fa-solid fa-chevron-right"></i></a>
            </div>
            <span><?php echo $login_err; ?></span>
        </form>
    </div>
</div>
<div class="imports_btn" style="text-align:center;">
    <a href="import_users.php">Importuokite vartotojus</a>
</div>
<?php include 'footer.php'; ?>