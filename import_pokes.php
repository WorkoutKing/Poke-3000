<?php
require_once 'db.php';
set_time_limit(300);

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["import_file"])) {
    $file = $_FILES["import_file"]["tmp_name"];
    $fileType = $_FILES["import_file"]["type"];
    $pokes = [];

    if ($fileType == "application/json") {
        $json_data = file_get_contents($file);
        $data = json_decode($json_data, true);

        if ($data !== NULL) {
            foreach ($data as $item) {
                if (isset($item['from'], $item['to'], $item['date'])) {
                    $sender_email = $conn->real_escape_string($item['from']);
                    $receiver_email = $conn->real_escape_string($item['to']);
                    $poke_date = $conn->real_escape_string($item['date']);

                    $sender_query = "SELECT id FROM users WHERE email = '$sender_email'";
                    $receiver_query = "SELECT id FROM users WHERE email = '$receiver_email'";

                    $sender_result = $conn->query($sender_query);
                    $receiver_result = $conn->query($receiver_query);

                    if ($sender_result->num_rows > 0 && $receiver_result->num_rows > 0) {
                        $sender_id = $sender_result->fetch_assoc()['id'];
                        $receiver_id = $receiver_result->fetch_assoc()['id'];

                        $pokes[] = "($sender_id, $receiver_id, '$poke_date')";
                    }
                }
            }
        }
    } elseif ($fileType == "text/csv") {
        $handle = fopen($file, "r");

        if ($handle !== FALSE) {
            fgetcsv($handle);

            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                $sender_email = $conn->real_escape_string($data[0]);
                $receiver_email = $conn->real_escape_string($data[1]);
                $poke_date = $conn->real_escape_string($data[2]);

                $sender_query = "SELECT id FROM users WHERE email = '$sender_email'";
                $receiver_query = "SELECT id FROM users WHERE email = '$receiver_email'";

                $sender_result = $conn->query($sender_query);
                $receiver_result = $conn->query($receiver_query);

                if ($sender_result->num_rows > 0 && $receiver_result->num_rows > 0) {
                    $sender_id = $sender_result->fetch_assoc()['id'];
                    $receiver_id = $receiver_result->fetch_assoc()['id'];

                    $pokes[] = "($sender_id, $receiver_id, '$poke_date')";
                }
            }
            fclose($handle);
        }
    }

    if (!empty($pokes)) {
        $values = implode(',', $pokes);
        $sql = "INSERT INTO pokes (sender_id, receiver_id, poke_date) VALUES $values";

        if ($conn->query($sql) === TRUE) {
            $_SESSION['message'] = "Pokes imported successfully.";
            $_SESSION['message_type'] = "success";
        } else {
            $_SESSION['message'] = "Error: " . $sql . "<br>" . $conn->error;
            $_SESSION['message_type'] = "error";
        }
    } else {
        $_SESSION['message'] = "No valid data to import.";
        $_SESSION['message_type'] = "error";
    }

    header("location: import_pokes.php");
    exit;
}
?>

<?php include 'header.php'; ?>

<?php
if (isset($_SESSION['message'])) {
    echo "<div class='message {$_SESSION['message_type']}'>{$_SESSION['message']}</div>";
    unset($_SESSION['message']);
    unset($_SESSION['message_type']);
}
?>
<div class="login-container">
    <div class="login-form">
        <h1 class="login_page_title">Importuokite pokes iš CSV arba JSON</h1>
        <form action="import_pokes.php" method="post" enctype="multipart/form-data">
            <label for="import_file">Pasirinkite CSV arba JSON failą:</label>
            <input type="file" name="import_file" id="import_file" accept=".csv, .json" required>
            <input type="submit" value="Import">
        </form>
    </div>
</div>

<?php include 'footer.php'; ?>