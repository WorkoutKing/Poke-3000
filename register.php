<?php

require_once 'db.php';

$nickname = $name = $surname = $email = $password = $confirm_password = "";
$nickname_err = $name_err = $surname_err = $email_err = $password_err = $confirm_password_err = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (empty(trim($_POST['nickname']))) {
        $nickname_err = "Prašome įvesti vartotojo vardą.";
    } else {
        $sql = "SELECT id FROM users WHERE nickname = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("s", $param_nickname);
            $param_nickname = trim($_POST["nickname"]);
            if ($stmt->execute()) {
                $stmt->store_result();
                if ($stmt->num_rows == 1) {
                    $nickname_err = "Vartotojo vardas užžimtas.";
                } else {
                    $nickname = trim($_POST['nickname']);
                }
            }
            $stmt->close();
        }
    }

    if (empty(trim($_POST['name']))) {
        $name_err = "Prašome įvesti vardą.";
    } else {
        $name = trim($_POST['name']);
    }

    if (empty(trim($_POST['surname']))) {
        $surname_err = "Prašome įvesti pavardę.";
    } else {
        $surname = trim($_POST['surname']);
    }

    if (empty(trim($_POST['email']))) {
        $email_err = "Prašome įvesti vartotojo el.paštą.";
    } else {
        $sql = "SELECT id FROM users WHERE email = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("s", $param_email);
            $param_email = trim($_POST["email"]);
            if ($stmt->execute()) {
                $stmt->store_result();
                if ($stmt->num_rows == 1) {
                    $email_err = "Vartotojo el.paštas užžimtas.";
                } else {
                    $email = trim($_POST['email']);
                }
            }
            $stmt->close();
        }
    }

    if (empty(trim($_POST['password']))) {
        $password_err = "Prašome įvesti slaptažodį.";
    } elseif (strlen(trim($_POST['password'])) < 6) {
        $password_err = "Slaptažodis negali būti trumpesnis nei 6 simboliai";
    } elseif (!preg_match('/[A-Z]/', trim($_POST['password']))) {
        $password_err = "Slaptažodyje turi būti bent viena didžioji raidė.";
    } elseif (!preg_match('/[0-9]/', trim($_POST['password']))) {
        $password_err = "Slaptažodyje turi būti bent vienas skaičius.";
    } else {
        $password = trim($_POST['password']);
    }

    if (empty(trim($_POST['confirm_password']))) {
        $confirm_password_err = "Prašome pakartoti slaptažodį.";
    } else {
        $confirm_password = trim($_POST['confirm_password']);
        if (empty($password_err) && ($password != $confirm_password)) {
            $confirm_password_err = "Slaptažodis nesutampa.";
        }
    }

    if (empty($nickname_err) && empty($name_err) && empty($surname_err) && empty($email_err) && empty($password_err) && empty($confirm_password_err)) {
        $sql = "INSERT INTO users (nickname, name, surname, email, password) VALUES (?, ?, ?, ?, ?)";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("sssss", $param_nickname, $param_name, $param_surname, $param_email, $param_password);
            $param_nickname = $nickname;
            $param_name = $name;
            $param_surname = $surname;
            $param_email = $email;
            $param_password = password_hash($password, PASSWORD_DEFAULT);

            if ($stmt->execute()) {
                header("location: login.php");
            } else {
                echo "Įvyko klaida, bandykite dar kartą.";
            }
            $stmt->close();
        }
    }
    $conn->close();
}
?>

<?php include 'header.php'; ?>
<div class="register-container">
    <div class="register-form">
        <h1 class="reg_page_title">REGISTRACIJA</h1>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <?php if ($nickname_err || $name_err || $surname_err || $email_err || $password_err || $confirm_password_err): ?>
                <div class="errors_container">
                    <span><?php echo $nickname_err; ?></span><br>
                    <span><?php echo $name_err; ?></span><br>
                    <span><?php echo $surname_err; ?></span><br>
                    <span><?php echo $email_err; ?></span><br>
                    <span><?php echo $password_err; ?></span><br>
                    <span><?php echo $confirm_password_err; ?></span>
                </div>
            <?php endif; ?>

            <div class="form-control">
                <label>Prisijungimo vardas</label>
                <input type="text" name="nickname" value="<?php echo $nickname; ?>">
            </div>

            <div class="form-control">
                <label>Vardas</label>
                <input type="text" name="name" value="<?php echo $name; ?>">
            </div>

            <div class="form-control">
                <label>Pavardė</label>
                <input type="text" name="surname" value="<?php echo $surname; ?>">
            </div>

            <div class="form-control">
                <label>El. paštas</label>
                <input type="text" name="email" value="<?php echo $email; ?>">
            </div>

            <div class="form-control">
                <label>Slaptažodis</label>
                <input type="password" name="password" value="<?php echo $password; ?>">
            </div>

            <div class="form-control">
                <label>Slaptažodžio pakartojimas</label>
                <input type="password" name="confirm_password" value="<?php echo $confirm_password; ?>">
            </div>

            <div class="reg_btn_pos">
                <button type="submit" class="material-button">Saugoti <i class="fa-solid fa-chevron-right"></i></button>
            </div>
        </form>
    </div>
</div>
<?php include 'footer.php'; ?>