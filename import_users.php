<?php
require_once 'db.php';
set_time_limit(300);

function generateRandomPassword($length = 8)
{
    $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    $password = '';
    $hasUppercase = false;
    $hasNumber = false;

    while (strlen($password) < $length || !$hasUppercase || !$hasNumber) {
        $char = $characters[rand(0, strlen($characters) - 1)];
        if (ctype_upper($char)) {
            $hasUppercase = true;
        }
        if (ctype_digit($char)) {
            $hasNumber = true;
        }
        $password .= $char;
    }

    return $password;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["import_file"])) {
    $file = $_FILES["import_file"]["tmp_name"];
    $fileType = $_FILES["import_file"]["type"];
    $users = [];

    if ($fileType == "application/json") {
        $json_data = file_get_contents($file);
        $data = json_decode($json_data, true);

        if ($data !== NULL) {
            foreach ($data as $item) {
                $nickname = $conn->real_escape_string($item['nickname']);
                $name = $conn->real_escape_string($item['name']);
                $surname = $conn->real_escape_string($item['surname']);
                $email = $conn->real_escape_string($item['email']);
                $password = generateRandomPassword();
                $password_hash = $conn->real_escape_string(password_hash($password, PASSWORD_DEFAULT));

                // Check if email already exists in the database
                $existing_email_query = "SELECT COUNT(*) AS count FROM users WHERE email = '$email'";
                $existing_email_result = $conn->query($existing_email_query);
                $existing_email_row = $existing_email_result->fetch_assoc();

                if ($existing_email_row['count'] == 0) {
                    $users[] = "('$nickname', '$name', '$surname', '$email', '$password_hash')";
                } else {
                    // Skip this entry as the email already exists
                    $_SESSION['message'] = "Skipped importing user with email '$email' as it already exists.";
                    $_SESSION['message_type'] = "warning";
                }
            }
        }
    } elseif ($fileType == "text/csv") {
        $handle = fopen($file, "r");

        if ($handle !== FALSE) {
            fgetcsv($handle);

            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                $nickname = $conn->real_escape_string($data[0]);
                $name = $conn->real_escape_string($data[1]);
                $surname = $conn->real_escape_string($data[2]);
                $email = $conn->real_escape_string($data[3]);
                $password = generateRandomPassword();
                $password_hash = $conn->real_escape_string(password_hash($password, PASSWORD_DEFAULT));

                // Check if email already exists in the database
                $existing_email_query = "SELECT COUNT(*) AS count FROM users WHERE email = '$email'";
                $existing_email_result = $conn->query($existing_email_query);
                $existing_email_row = $existing_email_result->fetch_assoc();

                if ($existing_email_row['count'] == 0) {
                    $users[] = "('$nickname', '$name', '$surname', '$email', '$password_hash')";
                } else {
                    // Skip this entry as the email already exists
                    $_SESSION['message'] = "Skipped importing user with email '$email' as it already exists.";
                    $_SESSION['message_type'] = "warning";
                }
            }
            fclose($handle);
        }
    }

    if (!empty($users)) {
        $values = implode(',', $users);
        $sql = "INSERT INTO users (nickname, name, surname, email, password) VALUES $values";

        if ($conn->query($sql) === TRUE) {
            $_SESSION['message'] = "Users imported successfully.";
            $_SESSION['message_type'] = "success";
        } else {
            if ($conn->errno == 1062) {
                $_SESSION['message'] = "Error: Duplicate entry found for email address.";
            } else {
                $_SESSION['message'] = "Error: " . $sql . "<br>" . $conn->error;
            }
            $_SESSION['message_type'] = "error";
        }
    } else {
        $_SESSION['message'] = "No valid data to import.";
        $_SESSION['message_type'] = "error";
    }
}
?>

<?php include 'header.php'; ?>

<?php
if (isset($_SESSION['message'])) {
    echo '<div class="message ' . $_SESSION['message_type'] . '">';
    echo $_SESSION['message'];
    echo '</div>';
    unset($_SESSION['message']);
    unset($_SESSION['message_type']);
}
?>
<div class="login-container">
    <div class="login-form">
        <h1 class="login_page_title">Importuokite vartotojus iš CSV arba JSON</h1>
        <form action="import_users.php" method="post" enctype="multipart/form-data">
            <label for="import_file">Pasirinkite CSV arba JSON failą:</label>
            <input type="file" name="import_file" id="import_file" accept=".csv, .json" required>
            <input type="submit" value="Import">
        </form>
        <?php
        echo '<a href="import_pokes.php" style="margin-top:50px;display:block;">Import Pokes</a>';
        ?>
    </div>
</div>
<?php include 'footer.php'; ?>