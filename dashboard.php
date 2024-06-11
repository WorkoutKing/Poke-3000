<?php

require_once 'db.php';

$page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$limit = 8;
$offset = ($page - 1) * $limit;

$search = isset($_GET['search']) ? $_GET['search'] : '';
$search_condition = $search ? "WHERE u.nickname LIKE '%$search%' OR u.name LIKE '%$search%' OR u.surname LIKE '%$search%' OR u.email LIKE '%$search%'" : '';

$sql = "SELECT u.id, u.nickname, u.name, u.surname, u.email, COUNT(p.receiver_id) AS poke_count
        FROM users u
        LEFT JOIN pokes p ON u.id = p.receiver_id
        $search_condition
        GROUP BY u.id
        LIMIT $limit OFFSET $offset";
$result = $conn->query($sql);
?>
<script>
    var typingTimer;
    var doneTypingInterval = 500;

    function debounce(func, delay) {
        clearTimeout(typingTimer);
        typingTimer = setTimeout(func, delay);
    }

    function searchUsers() {
        debounce(function () {
            var searchInput = document.getElementById("searchInput").value;
            window.location.href = "dashboard.php?search=" + searchInput;
        }, doneTypingInterval);
    }
</script>
<?php include 'header.php'; ?>
<div class="user_dash_pos">
    <div class="users-container">
        <h1 class="inside_page_title">Vartotojai</h1>
        <div class="search-pos">
            <i class="fas fa-search search-icon"></i>
            <input type="text" id="searchInput" placeholder="Ieškoti pagal vardą" onkeyup="searchUsers()">
        </div>

        <table>
            <thead>
                <tr>
                    <th>Vardas</th>
                    <th>Pavardė</th>
                    <th>El.paštas</th>
                    <th>Poke skaičius</th>
                    <th style="width:200px;"></th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row["name"]; ?></td>
                            <td><?php echo $row["surname"]; ?></td>
                            <td><?php echo $row["email"]; ?></td>
                            <td><?php echo $row["poke_count"]; ?></td>
                            <td>
                                <form action="send_poke.php" method="post">
                                    <input type="hidden" name="receiver_id" value="<?php echo $row["id"]; ?>">
                                    <span class="button_pos_pok"><input type="submit" value="Poke" class="poke-button"><i
                                            class="fa-solid fa-chevron-right"></i></span>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5">Vartotojų sąrašas tusčius.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <?php
        $sql = "SELECT COUNT(*) AS total FROM users u $search_condition";
        $result = $conn->query($sql);
        $row = $result->fetch_assoc();
        $total_pages = ceil($row["total"] / $limit);

        echo "<div class='pagination_pos'>";
        if ($page > 1) {
            echo "<a href='dashboard.php?page=" . ($page - 1) . "&search=" . $search . "' class='pagination-chevron'>&laquo;</a> ";
        }

        if ($page > 3) {
            echo "<a href='dashboard.php?page=1&search=" . $search . "'>1</a> ";
            echo "<span>...</span> ";
        }

        for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 3); $i++) {
            if ($i == $page) {
                echo "<a href='dashboard.php?page=" . $i . "&search=" . $search . "' class='active'>" . $i . "</a> ";
            } else {
                echo "<a href='dashboard.php?page=" . $i . "&search=" . $search . "'>" . $i . "</a> ";
            }
        }

        if ($page < $total_pages - 2) {
            echo "<span>...</span> ";
            echo "<a href='dashboard.php?page=" . $total_pages . "&search=" . $search . "'>" . $total_pages . "</a> ";
        }

        if ($page < $total_pages) {
            echo "<a href='dashboard.php?page=" . ($page + 1) . "&search=" . $search . "' class='pagination-chevron'>&raquo;</a> ";
        }
        echo "</div>";
        ?>
    </div>
</div>
<?php include 'footer.php'; ?>

<?php
$conn->close();