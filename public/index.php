<?php
require_once 'C:/xampp/htdocs/PHP/app/Models/ddbb_access.php';

$pdo = access_ddbb($dsn, $username, $password);

$genres_list = $pdo->query("SELECT DISTINCT title FROM genres ORDER BY title")->fetchAll(PDO::FETCH_COLUMN);
$platforms_list = $pdo->query("SELECT DISTINCT title FROM platforms ORDER BY title")->fetchAll(PDO::FETCH_COLUMN);

$search = $_GET['search'] ?? '';
$selected_genres = $_GET['genres'] ?? [];
$selected_platforms = $_GET['platforms'] ?? [];
$order_by = $_GET['order_by'] ?? 'title';
$order_dir = ($_GET['order_dir'] ?? 'ASC') === 'DESC' ? 'DESC' : 'ASC';

$games = get_games_with_details($dsn, $username, $password, $search, $selected_genres, $selected_platforms, $order_by, $order_dir);
?>


<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hardcore Gamers</title>

    <script>
        function fetchPageSize() {
            fetch('http://localhost/PHP/app/Models/api_access.php?get_page_size')
                .then(response => response.text())
                .then(data => {
                    document.getElementById('result').innerText = "Page Size: " + data;
                })
                .catch(error => {
                    console.error("Error obteniendo el page_size:", error);
                });
        }

        function applyFilters() {
            let searchQuery = document.getElementById("search").value.toLowerCase();
            let checkedGenres = Array.from(document.querySelectorAll('input[name="genres[]"]:checked')).map(el => el.value);
            let checkedPlatforms = Array.from(document.querySelectorAll('input[name="platforms[]"]:checked')).map(el => el.value);
            let orderBy = document.getElementById("order_by").value;
            let orderDir = document.getElementById("order_dir").value;

            let filteredGames = gamesData.filter(game => {
                let matchesSearch = game.title.toLowerCase().includes(searchQuery);
                let matchesGenre = checkedGenres.length === 0 || checkedGenres.some(genre => game.genres.includes(genre));
                let matchesPlatform = checkedPlatforms.length === 0 || checkedPlatforms.some(platform => game.platforms.includes(platform));
                return matchesSearch && matchesGenre && matchesPlatform;
            });

            filteredGames.sort((a, b) => {
                let valueA = a[orderBy] || "";
                let valueB = b[orderBy] || "";

                if (typeof valueA === "string") valueA = valueA.toLowerCase();
                if (typeof valueB === "string") valueB = valueB.toLowerCase();

                return (orderDir === "ASC") ? (valueA > valueB ? 1 : -1) : (valueA < valueB ? 1 : -1);
            });

            renderTable(filteredGames);
        }
    </script>

    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid black;
            padding: 8px;
            text-align: left;
            cursor: default !important;
        }

        th {
            cursor: pointer;
            background-color: #f2f2f2;
        }

        img {
            max-width: 100px;
        }
    </style>

</head>

<body>

    <h1>Importar Juegos desde RAWG.io</h1>
    <form action="http://localhost/PHP/app/Models/api_access.php?save_games" method="POST">
        <button type="submit">Importar Juegos</button>
    </form>

    <!-- <h2>Ver Page Size de RAWG.io</h2>
    <button onclick="fetchPageSize()">Obtener Page Size</button>
    <p id="result"></p> -->

    <h2>Lista de Juegos</h2>

    <form method="GET">
        <input type="text" name="search" placeholder="Búsqueda por nombre" value="<?= htmlspecialchars($search) ?>">

        <div class="filter-container">
            <div class="filter-section">
                <h3>Filtrar por Género</h3>
                <?php $selected_genres = is_array($selected_genres) ? $selected_genres : []; ?>
                <?php foreach ($genres_list as $genre): ?>
                    <label>
                        <input type="checkbox" name="genres[]" value="<?= htmlspecialchars($genre) ?>"
                            <?= in_array($genre, $selected_genres) ? 'checked' : '' ?>>
                        <?= htmlspecialchars($genre) ?>
                    </label><br>
                <?php endforeach; ?>
            </div>

            <div class="filter-section">
                <h3>Filtrar por Plataforma</h3>
                <?php $selected_platforms = is_array($selected_platforms) ? $selected_platforms : []; ?>
                <?php foreach ($platforms_list as $platform): ?>
                    <label>
                        <input type="checkbox" name="platforms[]" value="<?= htmlspecialchars($platform) ?>"
                            <?= in_array($platform, $selected_platforms) ? 'checked' : '' ?>>
                        <?= htmlspecialchars($platform) ?>
                    </label><br>
                <?php endforeach; ?>
            </div>
        </div>
        <button type="submit">Aplicar Filtros</button>
    </form>

    <table>
        <thead>
            <tr>
                <th>Imagen</th>
                <th><a href="?<?= http_build_query(array_merge($_GET, ['order_by' => 'title', 'order_dir' => $order_dir === 'ASC' ? 'DESC' : 'ASC'])) ?>">Título</a></th>
                <th>Género</th>
                <th>Plataformas</th>
                <th><a href="?<?= http_build_query(array_merge($_GET, ['order_by' => 'release_date', 'order_dir' => $order_dir === 'ASC' ? 'DESC' : 'ASC'])) ?>">Fecha de Salida</a></th>
                <th><a href="?<?= http_build_query(array_merge($_GET, ['order_by' => 'developer', 'order_dir' => $order_dir === 'ASC' ? 'DESC' : 'ASC'])) ?>">Desarrollador</a></th>
                <th><a href="?<?= http_build_query(array_merge($_GET, ['order_by' => 'rating', 'order_dir' => $order_dir === 'ASC' ? 'DESC' : 'ASC'])) ?>">Puntuación</a></th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($games)): ?>
                <?php foreach ($games as $game): ?>
                    <tr>
                        <td><img src="<?= htmlspecialchars($game['img']) ?>" alt="Imagen del juego"></td>
                        <td><?= htmlspecialchars($game['title']) ?></td>
                        <td><?= htmlspecialchars(implode(', ', $game['genres'])) ?></td>
                        <td><?= htmlspecialchars(implode(', ', $game['platforms'])) ?></td>
                        <td><?= htmlspecialchars($game['release_date']) ?></td>
                        <td><?= htmlspecialchars($game['developer']) ?></td>
                        <td><?= htmlspecialchars($game['rating']) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7">No se encontraron juegos con estos filtros.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

</body>

</html>