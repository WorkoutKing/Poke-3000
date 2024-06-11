<?php

session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

require_once 'db.php';

$name = $surname = $email = $password = $confirm_password = "";
$name_err = $surname_err = $email_err = $password_err = $confirm_password_err = "";

$sql = "SELECT name, surname, email FROM users WHERE id = ?";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $param_id);
    $param_id = $_SESSION["id"];

    if ($stmt->execute()) {
        $stmt->store_result();

        if ($stmt->num_rows == 1) {
            $stmt->bind_result($name, $surname, $email);
            $stmt->fetch();
        }
    }
    $stmt->close();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (empty(trim($_POST["name"]))) {
        $name_err = "Prašome įvesti vardą.";
    } else {
        $name = trim($_POST["name"]);
    }

    if (empty(trim($_POST["surname"]))) {
        $surname_err = "Prašome įvesti pavardę.";
    } else {
        $surname = trim($_POST["surname"]);
    }

    if (empty(trim($_POST["email"]))) {
        $email_err = "Prašome įvesti vartotojo el.paštą.";
    } elseif (!filter_var(trim($_POST["email"]), FILTER_VALIDATE_EMAIL)) {
        $email_err = "Please enter a valid email.";
    } else {
        $sql = "SELECT id FROM users WHERE email = ? AND id != ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("si", $param_email, $param_id);
            $param_email = trim($_POST["email"]);
            $param_id = $_SESSION["id"];

            if ($stmt->execute()) {
                $stmt->store_result();
                if ($stmt->num_rows == 1) {
                    $email_err = "Vartotojo el.paštas užžimtas.";
                } else {
                    $email = trim($_POST["email"]);
                }
            }
            $stmt->close();
        }
    }

    if (!empty(trim($_POST["password"]))) {
        if (strlen(trim($_POST["password"])) < 6) {
            $password_err = "Slaptažodis negali būti trumpesnis nei 6 simboliai";
        } elseif (!preg_match('/[A-Z]/', trim($_POST["password"]))) {
            $password_err = "Slaptažodyje turi būti bent viena didžioji raidė.";
        } elseif (!preg_match('/[0-9]/', trim($_POST["password"]))) {
            $password_err = "Slaptažodyje turi būti bent vienas skaičius.";
        } else {
            $password = trim($_POST["password"]);
        }

        if (empty(trim($_POST["confirm_password"]))) {
            $confirm_password_err = "Prašome pakartoti slaptažodį.";
        } else {
            $confirm_password = trim($_POST["confirm_password"]);
            if (empty($password_err) && ($password != $confirm_password)) {
                $confirm_password_err = "Slaptažodis nesutampa.";
            }
        }
    }

    if (empty($name_err) && empty($surname_err) && empty($email_err) && empty($password_err) && empty($confirm_password_err)) {
        if (!empty($password)) {
            $sql = "UPDATE users SET name = ?, surname = ?, email = ?, password = ? WHERE id = ?";
            if ($stmt = $conn->prepare($sql)) {
                $stmt->bind_param("ssssi", $param_name, $param_surname, $param_email, $param_password, $param_id);
                $param_name = $name;
                $param_surname = $surname;
                $param_email = $email;
                $param_password = password_hash($password, PASSWORD_DEFAULT);
                $param_id = $_SESSION["id"];

                if ($stmt->execute()) {
                    header("location: dashboard.php");
                } else {
                    echo "Something went wrong. Please try again later.";
                }
                $stmt->close();
            }
        } else {
            $sql = "UPDATE users SET name = ?, surname = ?, email = ? WHERE id = ?";
            if ($stmt = $conn->prepare($sql)) {
                $stmt->bind_param("sssi", $param_name, $param_surname, $param_email, $param_id);
                $param_name = $name;
                $param_surname = $surname;
                $param_email = $email;
                $param_id = $_SESSION["id"];

                if ($stmt->execute()) {
                    header("location: dashboard.php");
                } else {
                    echo "Įvyko klaida, bandykite dar kartą.";
                }
                $stmt->close();
            }
        }
    }

    $conn->close();
}
?>

<?php include 'header.php'; ?>
<div class="register-container">
    <div class="register-form">
        <h1 class="reg_page_title">DUOMENŲ REDAGAVIMAS</h1>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <?php if ($name_err || $surname_err || $email_err || $password_err || $confirm_password_err): ?>
                <div class="errors_container">
                    <span><?php echo $name_err; ?></span><br>
                    <span><?php echo $surname_err; ?></span><br>
                    <span><?php echo $email_err; ?></span><br>
                    <span><?php echo $password_err; ?></span><br>
                    <span><?php echo $confirm_password_err; ?></span>
                </div>
            <?php endif; ?>
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
                <label>Slaptažodis<i style="font-size:12px;">(Palikti tuščią nekeičiant)</i></label>
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