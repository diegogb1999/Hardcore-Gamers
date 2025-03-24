<?php
require_once __DIR__ . '/../../config/sensible_data.php';

function access_ddbb($dsn, $username, $password)
{
    try {
        $pdo = new PDO($dsn, $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
    } catch (PDOException $e) {
        die("Error de conexión a la base de datos: " . $e->getMessage());
    }
    return $pdo;
}

function get_games_with_details($dsn, $username, $password, $search, $selected_genres, $selected_platforms, $order_by, $order_dir, $items_per_page, $offset)
{
    $pdo = access_ddbb($dsn, $username, $password);

    $allowed_columns = ['title', 'release_date', 'developer', 'rating'];
    if (!in_array($order_by, $allowed_columns)) {
        $order_by = 'title';
    }

    $query = "
    SELECT 
        g.id, g.title, g.release_date, g.developer, g.img, g.rating,
        (SELECT GROUP_CONCAT(DISTINCT gen.title ORDER BY gen.title SEPARATOR ', ') 
         FROM genres gen JOIN games_genres gg ON gen.id = gg.genre_id WHERE gg.game_id = g.id) AS genres,
        (SELECT GROUP_CONCAT(DISTINCT plat.title ORDER BY plat.title SEPARATOR ', ') 
         FROM platforms plat JOIN games_platforms gp ON plat.id = gp.platform_id WHERE gp.game_id = g.id) AS platforms
    FROM games g
    WHERE g.title LIKE ?";

    $params = ["%$search%"];

    if (!empty($selected_genres)) {
        $placeholders = implode(',', array_fill(0, count($selected_genres), '?'));
        $query .= " AND g.id IN (
            SELECT gg.game_id FROM games_genres gg 
            JOIN genres gen ON gg.genre_id = gen.id 
            WHERE gen.title IN ($placeholders) 
            GROUP BY gg.game_id 
            HAVING COUNT(DISTINCT gen.id) = ?
        )";
        $params = array_merge($params, $selected_genres);
        $params[] = count($selected_genres);
    }

    if (!empty($selected_platforms)) {
        $placeholders = implode(',', array_fill(0, count($selected_platforms), '?'));
        $query .= " AND g.id IN (
            SELECT gp.game_id FROM games_platforms gp 
            JOIN platforms plat ON gp.platform_id = plat.id 
            WHERE plat.title IN ($placeholders) 
            GROUP BY gp.game_id 
            HAVING COUNT(DISTINCT plat.id) = ?
        )";
        $params = array_merge($params, $selected_platforms);
        $params[] = count($selected_platforms);
    }

    $query .= " ORDER BY $order_by $order_dir LIMIT " . intval($items_per_page) . " OFFSET " . intval($offset);


    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $games = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($games as &$game) {
        $game['genres'] = isset($game['genres']) ? explode(', ', $game['genres']) : [];
        $game['platforms'] = isset($game['platforms']) ? explode(', ', $game['platforms']) : [];
    }

    return $games;
}

function count_total_games($dsn, $username, $password, $search, $selected_genres, $selected_platforms)
{
    $pdo = access_ddbb($dsn, $username, $password);

    $query = "SELECT COUNT(DISTINCT g.id) FROM games g WHERE g.title LIKE ?";
    $params = ["%$search%"];

    if (!empty($selected_genres)) {
        $placeholders = implode(',', array_fill(0, count($selected_genres), '?'));
        $query .= " AND g.id IN (
            SELECT gg.game_id FROM games_genres gg 
            JOIN genres gen ON gg.genre_id = gen.id 
            WHERE gen.title IN ($placeholders) 
            GROUP BY gg.game_id 
            HAVING COUNT(DISTINCT gen.id) = ?
        )";
        $params = array_merge($params, $selected_genres);
        $params[] = count($selected_genres);
    }

    if (!empty($selected_platforms)) {
        $placeholders = implode(',', array_fill(0, count($selected_platforms), '?'));
        $query .= " AND g.id IN (
            SELECT gp.game_id FROM games_platforms gp 
            JOIN platforms plat ON gp.platform_id = plat.id 
            WHERE plat.title IN ($placeholders) 
            GROUP BY gp.game_id 
            HAVING COUNT(DISTINCT plat.id) = ?
        )";
        $params = array_merge($params, $selected_platforms);
        $params[] = count($selected_platforms);
    }

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    return $stmt->fetchColumn();
}
