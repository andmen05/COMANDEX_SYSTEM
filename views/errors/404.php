<?php
// views/errors/404.php
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>404 — Comandex</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;700&display=swap" rel="stylesheet">
    <style>body{font-family:'Inter',sans-serif;}</style>
</head>
<body class="min-h-screen bg-gray-50 flex items-center justify-center">
    <div class="text-center">
        <p class="text-8xl font-black text-gray-200">404</p>
        <h1 class="text-xl font-bold text-gray-700 mt-4">Página no encontrada</h1>
        <p class="text-gray-400 mt-2">La ruta que buscas no existe.</p>
        <a href="<?= BASE_URL ?>/dashboard" class="mt-6 inline-block px-6 py-3 bg-blue-600 text-white rounded-xl font-semibold hover:bg-blue-700 transition-colors">
            Ir al Dashboard
        </a>
    </div>
</body>
</html>
