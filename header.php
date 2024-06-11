<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
} ?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Poke 3000</title>
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700&display=swap" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@material-ui/core@latest/dist/material-ui.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" />
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            loadNotifications();
        });

        function loadNotifications() {
            const xhr = new XMLHttpRequest();
            xhr.open('GET', 'fetch_notifications.php', true);
            xhr.onload = function () {
                if (this.status === 200) {
                    const response = JSON.parse(this.responseText);
                    const notificationsDropdown = document.getElementById('notifications_dropdown');

                    notificationsDropdown.innerHTML = '';
                    response.notifications.forEach(notification => {
                        notificationsDropdown.innerHTML += `
                            <a href="#">Poke nuo <b>${notification.name} ${notification.surname}</b></a>
                        `;
                    });

                    notificationsDropdown.innerHTML += `<a class="all_pokes_link" href="pokes_dashboard.php">VISI POKE ></a>`;
                }
            };
            xhr.send();
        }
    </script>
</head>

<body>
    <?php if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true): ?>
        <nav>
            <div class="nav-wrapper">
                <a href="/" class="brand-logo">BAKSNOTOJOS 3000</a>
                <ul id="nav-mobile" class="right hide-on-med-and-down">
                    <li>
                        <div class="dropdown">
                            <a><i class="fa-solid fa-hand-point-right"></i></a>
                            <div id="notifications_dropdown" class="dropdown-content">
                            </div>
                        </div>
                    </li>
                    <li><a href="edit.php"><i class="fa-solid fa-circle-user"></i></a></li>
                    <li><a href="logout.php"><i class=" fa-solid fa-right-from-bracket"></i></a></li>
                </ul>
            </div>
        </nav>
    <?php else: ?>
        <nav>
            <div class="nav-wrapper">
                <a href="/" class="brand-logo">BAKSNOTOJOS 3000</a>
                <ul id="nav-mobile" class="right hide-on-med-and-down">
                    <li><a href="login.php"><i class="fa-solid fa-hand-point-right"></i></a></li>
                    <li><a href="login.php"><i class="fa-solid fa-circle-user"></i></a></li>
                    <li><a href="login.php"><i class=" fa-solid fa-right-from-bracket"></i></a></li>
                </ul>
            </div>
        </nav>
    <?php endif; ?>