USE Hardcore_Gamers;
SHOW TABLES;

SELECT * FROM achievements;
SELECT * FROM games;
SELECT * FROM games_genres;
SELECT * FROM games_platforms;
SELECT * FROM genres;
SELECT * FROM platforms;
SELECT * FROM users;
SELECT * FROM users_achievements;

SELECT COUNT(*) FROM games;
SELECT COUNT(id) FROM games;
SELECT * FROM games WHERE slug = 'crash-bandicoot-warped';

OPTIMIZE TABLE games;

TRUNCATE TABLE games;

