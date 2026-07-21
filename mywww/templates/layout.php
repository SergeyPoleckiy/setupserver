<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $this->e($siteTitle) ?></title>
    <link rel="stylesheet" href="<?= $this->e($siteUrl) ?>/assets/css/terminal.css">
</head>
<body>
    <div class="terminal">
        <!-- Заголовок терминала -->
        <header class="terminal-header">
            <div class="buttons">
                <span class="btn-red">●</span>
                <span class="btn-yellow">●</span>
                <span class="btn-green">●</span>
            </div>
            <div class="title"><?= $this->e($siteTitle) ?></div>
        </header>

        <!-- Навигационное меню -->
        <nav class="terminal-nav">
            <a href="<?= $this->e($siteUrl) ?>/" class="<?= ($activeNav ?? '') === 'home' ? 'active' : '' ?>">~/главная</a>
            <a href="<?= $this->e($siteUrl) ?>/series/" class="<?= ($activeNav ?? '') === 'series' ? 'active' : '' ?>">~/серии</a>
            <a href="<?= $this->e($siteUrl) ?>/archive/" class="<?= ($activeNav ?? '') === 'archive' ? 'active' : '' ?>">~/архив</a>
        </nav>

        <!-- Тело терминала -->
        <main class="terminal-body">
            <?= $content ?>
        </main>

        <!-- Подвал / Статус-бар -->
        <footer class="terminal-footer">
            <span class="prompt">user01@srv02:~$</span>
            <span class="command">cat footer.txt</span>
            <div class="footer-info">
                © <?= date('Y') ?> srv02. All rights reserved. <span class="blink-cursor">█</span>
            </div>
        </footer>
    </div>
</body>
</html>
