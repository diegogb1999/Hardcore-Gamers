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

$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$items_per_page = $_GET['items_per_page'] ?? 10;
$offset = ($page - 1) * $items_per_page;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

$games = get_games_with_details($dsn, $username, $password, $search, $selected_genres, $selected_platforms, $order_by, $order_dir, $items_per_page, $offset);
$total_games = count_total_games($dsn, $username, $password, $search, $selected_genres, $selected_platforms);
$total_pages = ceil($total_games / $items_per_page);
?>


<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../public/assets/css/index.css">
    <link rel="icon" type="image/x-icon" href="../images/favicon.ico">
    <title>Hardcore Gamers</title>
</head>

<body>

    <h1>Importar Juegos desde RAWG.io</h1>
    <form action="http://localhost/PHP/app/Models/api_access.php?save_games" method="POST">
        <button type="submit">Importar Juegos</button>
    </form>

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

        <label for="items_per_page">Mostrar:</label>
        <select name="items_per_page" id="items_per_page">
            <option value="5" <?= ($items_per_page == 5) ? 'selected' : '' ?>>5</option>
            <option value="10" <?= ($items_per_page == 10) ? 'selected' : '' ?>>10</option>
            <option value="20" <?= ($items_per_page == 20) ? 'selected' : '' ?>>20</option>
            <option value="50" <?= ($items_per_page == 50) ? 'selected' : '' ?>>50</option>
            <option value="100" <?= ($items_per_page == 100) ? 'selected' : '' ?>>100</option>
            <option value="500" <?= ($items_per_page == 500) ? 'selected' : '' ?>>500</option>
        </select>

        <button type="submit">Aplicar Filtros</button>

    </form>

    <nav class="pagination">
        <ul style="list-style: none; display: flex; gap: 5px;">
            <li class="<?= ($current_page == 1) ? 'disabled' : '' ?>">
                <a href="?<?= http_build_query(array_merge($_GET, ['page' => 1])) ?>">⏪</a>
            </li>
            <li class="<?= ($current_page == 1) ? 'disabled' : '' ?>">
                <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>">◀️</a>
            </li>

            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <li class="<?= ($current_page == $i) ? 'active' : '' ?>">
                    <a href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"
                        style="<?= $i == $page ? 'font-weight: bold; text-decoration: underline;' : '' ?>">
                        <?= $i ?>
                    </a>
                </li>
            <?php endfor; ?>

            <li class="<?= ($current_page == $total_pages) ? 'disabled' : '' ?>">
                <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>">▶️</a>
            </li>
            <li class="<?= ($current_page == $total_pages) ? 'disabled' : '' ?>">
                <a href="?<?= http_build_query(array_merge($_GET, ['page' => $total_pages])) ?>">⏩</a>
            </li>
        </ul>
    </nav>

    <table>
        <thead>
            <tr>
                <th></th>
                <th><a href="?<?= http_build_query(array_merge($_GET, ['order_by' => 'title', 'order_dir' => $order_dir === 'ASC' ? 'DESC' : 'ASC'])) ?>">Juego</a></th>
                <th>Género</th>
                <th>Plataformas</th>
                <th><a href="?<?= http_build_query(array_merge($_GET, ['order_by' => 'release_date', 'order_dir' => $order_dir === 'ASC' ? 'DESC' : 'ASC'])) ?>">Lanzamiento</a></th>
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