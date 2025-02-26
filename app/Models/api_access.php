<?php

require 'C:/xampp/htdocs/PHP/config/sensible_data.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    try {
        $pdo = new PDO($dsn, $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
    } catch (PDOException $e) {
        die("Error de conexión a la base de datos: " . $e->getMessage());
    }

    // Función para obtener datos desde RAWG.io
    function fetch_from_rawg($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($ch);
        curl_close($ch);
        return json_decode($response, true);
    }

    // Obtener y guardar juegos en la base de datos
    function save_games($pdo, $api_key)
    {
        ini_set('max_execution_time', 3600);

        $page = 1;
        $games_fetched = 0;
        $max_games = 1000;

        while ($games_fetched < $max_games) {
            $url = "https://api.rawg.io/api/games?page=$page&page_size=40&key=$api_key";
            $data = fetch_from_rawg($url);

            if (!isset($data['results'])) {
                die("Error al obtener datos de la API.");
            }

            foreach ($data['results'] as $game) {

                $game_detail = fetch_from_rawg("https://api.rawg.io/api/games/{$game['id']}?key=$api_key");

                $stmt = $pdo->prepare("INSERT INTO games (title, info, release_date, developer, img, purchase_url, slug, rating, trailer, achievements_amount) 
                                   VALUES (:title, :info, :release_date, :developer, :img, :purchase_url, :slug, :rating, :trailer, :achievements)");
                $stmt->execute([
                    ':title' => $game_detail['name'],
                    ':info' => $game_detail['description'] ?? '',
                    ':release_date' => $game_detail['released'] ?? null,
                    ':developer' => $game_detail['developers'][0]['name'] ?? 'Desconocido',
                    ':img' => $game_detail['background_image'] ?? '',
                    ':purchase_url' => $game_detail['website'] ?? '',
                    ':slug' => $game_detail['slug'],
                    ':rating' => $game_detail['rating'],
                    ':trailer' => $game_detail['movies'][0]['data']['max'] ?? '',
                    ':achievements' => $game_detail['achievements_count'] ?? 0
                ]);
                $game_id = $pdo->lastInsertId();

                if (!empty($game_detail['genres'])) {
                    foreach ($game_detail['genres'] as $genre) {
                        $stmt = $pdo->prepare("INSERT IGNORE INTO genres (title, slug, img) VALUES (:title, :slug, :img)");
                        $stmt->execute([
                            ':title' => $genre['name'],
                            ':slug' => $genre['slug'],
                            ':img' => $genre['image_background'] ?? ''
                        ]);

                        $genre_id = $pdo->lastInsertId();
                        if (!$genre_id) {
                            $stmt = $pdo->prepare("SELECT id FROM genres WHERE slug = :slug");
                            $stmt->execute([':slug' => $genre['slug']]);
                            $genre_id = $stmt->fetchColumn();
                        }

                        $stmt = $pdo->prepare("INSERT IGNORE INTO games_genres (game_id, genre_id) VALUES (:game_id, :genre_id)");
                        $stmt->execute([
                            ':game_id' => $game_id,
                            ':genre_id' => $genre_id
                        ]);
                    }
                }

                if (!empty($game_detail['platforms'])) {
                    foreach ($game_detail['platforms'] as $platform) {
                        $stmt = $pdo->prepare("INSERT IGNORE INTO platforms (title, slug, img) VALUES (:title, :slug, :img)");
                        $stmt->execute([
                            ':title' => $platform['platform']['name'],
                            ':slug' => $platform['platform']['slug'],
                            ':img' => $platform['platform']['image_background'] ?? ''
                        ]);

                        $platform_id = $pdo->lastInsertId();
                        if (!$platform_id) {
                            $stmt = $pdo->prepare("SELECT id FROM platforms WHERE slug = :slug");
                            $stmt->execute([':slug' => $platform['platform']['slug']]);
                            $platform_id = $stmt->fetchColumn();
                        }

                        $stmt = $pdo->prepare("INSERT IGNORE INTO games_platforms (game_id, platform_id) VALUES (:game_id, :platform_id)");
                        $stmt->execute([
                            ':game_id' => $game_id,
                            ':platform_id' => $platform_id
                        ]);
                    }
                }

                $achievements = fetch_from_rawg("https://api.rawg.io/api/games/{$game['id']}/achievements?key=$api_key");
                if (!empty($achievements['results'])) {
                    foreach ($achievements['results'] as $achievement) {
                        $stmt = $pdo->prepare("INSERT INTO achievements (game_id, title, info, img) VALUES (:game_id, :title, :info, :img)");
                        $stmt->execute([
                            ':game_id' => $game_id,
                            ':title' => $achievement['name'],
                            ':info' => $achievement['description'],
                            ':img' => $achievement['image'] ?? ''
                        ]);
                    }
                }

                $games_fetched++;
                if ($games_fetched >= $max_games) break;
            }

            $page++;
        }
    }

    save_games($pdo, $api_key);

    echo "¡Importación completada con éxito!";
} else {
    echo "Acceso no autorizado.";
}
