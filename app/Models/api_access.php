<?php
require_once 'C:/xampp/htdocs/PHP/app/Models/ddbb_access.php';

function fetch_from_rawg($url)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_FAILONERROR, true);
    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode($response, true);
}

function game_exists($pdo, $slug)
{
    $stmt = $pdo->prepare("SELECT 1 FROM games WHERE slug = ? LIMIT 1");
    $stmt->execute([$slug]);
    return $stmt->fetchColumn();
}

function insert_game($pdo, $game_detail)
{
    $stmt = $pdo->prepare("CALL InsertGame(:title, :info, :release_date, :developer, :img, :purchase_url, :slug, :rating, :trailer, :achievements, @game_id)");
    $stmt->execute([
        ':title' => $game_detail['name'],
        ':info' => strip_tags($game_detail['description'] ?? ''),
        ':release_date' => $game_detail['released'] ?? null,
        ':developer' => $game_detail['developers'][0]['name'] ?? 'Desconocido',
        ':img' => $game_detail['background_image'] ?? '',
        ':purchase_url' => $game_detail['website'] ?? '',
        ':slug' => $game_detail['slug'],
        ':rating' => $game_detail['rating'],
        ':trailer' => $game_detail['movies'][0]['data']['max'] ?? '',
        ':achievements' => $game_detail['achievements_count'] ?? 0
    ]);
    return $pdo->query("SELECT @game_id")->fetchColumn();
}

function insert_genres($pdo, $game_id, $genres)
{
    foreach ($genres as $genre) {
        $stmt = $pdo->prepare("CALL InsertGenre(:title, :slug, :img, @genre_id)");
        $stmt->execute([
            ':title' => $genre['name'],
            ':slug' => $genre['slug'],
            ':img' => $genre['image_background'] ?? ''
        ]);
        $genre_id = $pdo->query("SELECT @genre_id")->fetchColumn();

        $stmt = $pdo->prepare("CALL InsertGameGenre(:game_id, :genre_id)");
        $stmt->execute([
            ':game_id' => $game_id,
            ':genre_id' => $genre_id
        ]);
    }
}

function insert_platforms($pdo, $game_id, $platforms)
{
    foreach ($platforms as $platform) {
        $stmt = $pdo->prepare("CALL InsertPlatform(:title, :slug, :img, @platform_id)");
        $stmt->execute([
            ':title' => $platform['platform']['name'],
            ':slug' => $platform['platform']['slug'],
            ':img' => $platform['platform']['image_background'] ?? ''
        ]);
        $platform_id = $pdo->query("SELECT @platform_id")->fetchColumn();

        $stmt = $pdo->prepare("CALL InsertGamePlatform(:game_id, :platform_id)");
        $stmt->execute([
            ':game_id' => $game_id,
            ':platform_id' => $platform_id
        ]);
    }
}

function insert_achievements($pdo, $game_id, $game_api_id, $api_key)
{
    $achievements = fetch_from_rawg("https://api.rawg.io/api/games/{$game_api_id}/achievements?key=$api_key");
    if (!empty($achievements['results'])) {
        foreach ($achievements['results'] as $achievement) {
            $stmt = $pdo->prepare("CALL InsertAchievement(:game_id, :title, :info, :img)");
            $stmt->execute([
                ':game_id' => $game_id,
                ':title' => $achievement['name'],
                ':info' => $achievement['description'],
                ':img' => $achievement['image'] ?? ''
            ]);
        }
    }
}

function save_games($pdo, $api_key)
{
    ini_set('max_execution_time', 60);

    $page = 1;
    $games_fetched = 0;
    $max_games = 40;

    while ($games_fetched < $max_games) {
        $url = "https://api.rawg.io/api/games?page=$page&key=$api_key";
        $data = fetch_from_rawg($url);

        if (!isset($data['results'])) {
            die("Error al obtener datos de la API.");
        }

        foreach ($data['results'] as $game) {
            if (game_exists($pdo, $game['slug'])) {
                echo "El juego '{$game['name']}' ya existe en la base de datos (slug: {$game['slug']}). Pasando al siguiente...\n";
                continue;
            }

            $game_detail = fetch_from_rawg("https://api.rawg.io/api/games/{$game['id']}?key=$api_key");

            $game_id = insert_game($pdo, $game_detail);
            if ($game_id) {
                if (!empty($game_detail['genres'])) {
                    insert_genres($pdo, $game_id, $game_detail['genres']);
                }
                if (!empty($game_detail['platforms'])) {
                    insert_platforms($pdo, $game_id, $game_detail['platforms']);
                }
                insert_achievements($pdo, $game_id, $game['id'], $api_key);
            }

            $games_fetched++;
            if ($games_fetched >= $max_games) break;
        }

        $page++;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $pdo = access_ddbb($dsn, $username, $password);
    save_games($pdo, $api_key);

    echo "¡Importación completada con éxito!";
    exit;
}
