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

DELETE FROM games;
DELETE FROM genres;
DELETE FROM platforms;

ALTER TABLE achievements AUTO_INCREMENT = 1;
ALTER TABLE games AUTO_INCREMENT = 1;
ALTER TABLE genres AUTO_INCREMENT = 1;
ALTER TABLE platforms AUTO_INCREMENT = 1;

DROP DATABASE hardcore_gamers;