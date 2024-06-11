<?php
session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}
?>
<?php include 'header.php'; ?>

<div class="user_dash_pos">
    <div class="users-container">
        <h1 class="inside_page_title">POKE ISTORIJA</h1>
        <div class="search_pos">
            <input type="text" id="search_name" name="search_name" placeholder="Ieškoti pagal vardą">
            <i class="fas fa-search"></i>

            <input type="date" id="date_from" name="date_from" placeholder="Data nuo">

            <input type="date" id="date_to" name="date_to" placeholder="Data iki">
        </div>

        <table>
            <thead>
                <tr>
                    <th>Data</th>
                    <th>Siuntėjas</th>
                    <th>Gavėjas</th>
                </tr>
            </thead>
            <tbody id="pokes_table_body">
            </tbody>
        </table>
        <div id="pagination_pos" class="pagination_pos">
        </div>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        loadPokes(1);

        document.getElementById('search_name').addEventListener('input', function () {
            loadPokes(1);
        });
        document.getElementById('date_from').addEventListener('change', function () {
            loadPokes(1);
        });
        document.getElementById('date_to').addEventListener('change', function () {
            loadPokes(1);
        });
    });

    function loadPokes(page) {
        const searchName = document.getElementById('search_name').value;
        const dateFrom = document.getElementById('date_from').value;
        const dateTo = document.getElementById('date_to').value;

        const xhr = new XMLHttpRequest();
        xhr.open('GET', `fetch_pokes.php?page=${page}&search_name=${searchName}&date_from=${dateFrom}&date_to=${dateTo}`, true);
        xhr.onload = function () {
            if (this.status === 200) {
                const response = JSON.parse(this.responseText);
                const tableBody = document.getElementById('pokes_table_body');
                const paginationControls = document.getElementById('pagination_pos');

                tableBody.innerHTML = '';
                response.pokes.forEach(poke => {
                    tableBody.innerHTML += `
                    <tr>
                        <td>${poke.poke_date}</td>
                        <td>${poke.sender_name} ${poke.sender_surname}</td>
                        <td>${poke.receiver_name} ${poke.receiver_surname}</td>
                    </tr>
                `;
                });

                paginationControls.innerHTML = '';
                if (response.total_pages > 0) {
                    paginationControls.innerHTML = createPagination(page, response.total_pages, searchName, dateFrom, dateTo);
                }
            }
        };
        xhr.send();
    }

    function createPagination(currentPage, totalPages, searchName, dateFrom, dateTo) {
        let paginationHTML = "<div class='pagination_pos'>";

        if (currentPage > 1) {
            paginationHTML += `<a class='pagination-chevron' onclick='loadPokes(${currentPage - 1})'>&laquo;</a> `;
        }

        if (currentPage > 3) {
            paginationHTML += `<a onclick='loadPokes(1)'>1</a> <span>...</span> `;
        }

        for (let i = Math.max(1, currentPage - 2); i <= Math.min(totalPages, currentPage + 2); i++) {
            if (i === currentPage) {
                paginationHTML += `<a class='active' onclick='loadPokes(${i})'>${i}</a> `;
            } else {
                paginationHTML += `<a onclick='loadPokes(${i})'>${i}</a> `;
            }
        }

        if (currentPage < totalPages - 2) {
            paginationHTML += `<span>...</span> <a onclick='loadPokes(${totalPages})'>${totalPages}</a> `;
        }

        if (currentPage < totalPages) {
            paginationHTML += `<a class='pagination-chevron' onclick='loadPokes(${currentPage + 1})'>&raquo;</a>`;
        }

        paginationHTML += "</div>";
        return paginationHTML;
    }
</script>

<?php include 'footer.php'; ?>