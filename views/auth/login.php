<?php
// views/auth/login.php
?>
<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar sesión — Comandex POS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.min.js"></script>
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="h-full bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 flex items-center justify-center p-4">

<div class="w-full max-w-md">
    <!-- Logo -->
    <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-br from-amber-400 to-amber-600 rounded-2xl shadow-2xl mb-4">
            <span class="text-white font-bold text-3xl">C</span>
        </div>
        <h1 class="text-2xl font-bold text-white">Comandex POS</h1>
        <p class="text-slate-400 text-sm mt-1">Sistema de gestión para restaurantes</p>
    </div>

    <!-- Card -->
    <div class="bg-white/5 backdrop-blur-xl border border-white/10 rounded-3xl p-8 shadow-2xl">
        <h2 class="text-lg font-semibold text-white mb-6">Iniciar sesión</h2>

        <?php if (!empty($error)): ?>
        <div class="mb-4 flex items-center gap-2 bg-red-500/20 border border-red-500/30 rounded-xl px-4 py-3 text-red-300 text-sm">
            <i data-lucide="alert-circle" class="w-4 h-4 flex-shrink-0"></i>
            <?= htmlspecialchars($error) ?>
        </div>
        <?php endif; ?>

        <form method="POST" action="<?= BASE_URL ?>/login" class="space-y-5">
            <div>
                <label class="block text-sm font-medium text-slate-300 mb-1.5">Correo electrónico</label>
                <div class="relative">
                    <i data-lucide="mail" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400"></i>
                    <input
                        type="email" name="email" id="email" required
                        placeholder="usuario@restaurante.com"
                        value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                        class="w-full pl-10 pr-4 py-3 bg-white/10 border border-white/20 rounded-xl text-white placeholder-slate-500 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-300 mb-1.5">Contraseña</label>
                <div class="relative">
                    <i data-lucide="lock" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400"></i>
                    <input
                        type="password" name="password" id="password" required
                        placeholder="••••••••"
                        class="w-full pl-10 pr-4 py-3 bg-white/10 border border-white/20 rounded-xl text-white placeholder-slate-500 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition">
                </div>
            </div>
            <button type="submit" id="btn-login"
                class="w-full py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-xl transition-all shadow-lg shadow-blue-600/30 flex items-center justify-center gap-2">
                <i data-lucide="log-in" class="w-4 h-4"></i>
                Entrar al sistema
            </button>
        </form>

        <!-- Demo credentials -->
        <div class="mt-6 p-4 bg-white/5 rounded-xl border border-white/10">
            <p class="text-xs font-semibold text-slate-400 mb-2 uppercase tracking-wider">Credenciales de demo</p>
            <div class="space-y-1 text-xs text-slate-400">
                <p><span class="text-slate-300 font-medium">Email:</span> diego.ramirez@laterraza.com</p>
                <p><span class="text-slate-300 font-medium">Contraseña:</span> Admin1234</p>
            </div>
        </div>
    </div>

    <p class="text-center text-slate-600 text-xs mt-6">© <?= date('Y') ?> Comandex POS · v<?= APP_VERSION ?></p>
</div>

<script>
    lucide.createIcons();
</script>
</body>
</html>
