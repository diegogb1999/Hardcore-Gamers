<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hardcore Gamers</title>
    <script>
        function fetchPageSize() {
            fetch('http://localhost/PHP/app/Models/api_access.php?get_page_size') // Asegúrate de que la URL es correcta
                .then(response => response.text())
                .then(data => {
                    document.getElementById('result').innerText = "Page Size: " + data;
                })
                .catch(error => {
                    console.error("Error obteniendo el page_size:", error);
                });
        }
    </script>
</head>

<body>
    <h1>Importar Juegos desde RAWG.io</h1>
    <form action="http://localhost/PHP/app/Models/api_access.php" method="POST">
        <button type="submit">Importar Juegos</button>
    </form>

    <h2>Ver Page Size de RAWG.io</h2>
    <button onclick="fetchPageSize()">Obtener Page Size</button>
    <p id="result"></p>
</body>

</html>