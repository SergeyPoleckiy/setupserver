<?php

namespace Admin;

require_once __DIR__ . '/../vendor/autoload.php';

session_start();

use Scripts\Config;

$action = $_GET['action'] ?? 'login';
$crud = new Crud();
$error = '';
$success = '';

// Обработка POST-запросов
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'login') {
        if (Auth::login($_POST['password'] ?? '')) {
            header('Location: ?action=dashboard');
            exit;
        }
        $error = 'Неверный пароль';
        $action = 'login';
    } elseif ($action === 'logout') {
        Auth::logout();
        header('Location: ?action=login');
        exit;
    } elseif ($action === 'series-save') {
        Auth::requireAuth();
        $slug = $_POST['slug'] ?? '';
        $title = $_POST['title'] ?? '';
        $desc = $_POST['description'] ?? '';
        $prereqs = array_filter(explode("\n", $_POST['prerequisites'] ?? ''));
        $crud->saveSeries($slug, $title, $desc, $prereqs);
        $crud->rebuild();
        header('Location: ?action=series');
        exit;
    } elseif ($action === 'series-delete') {
        Auth::requireAuth();
        $crud->deleteSeries($_GET['slug'] ?? '');
        $crud->rebuild();
        header('Location: ?action=series');
        exit;
    } elseif ($action === 'chapter-save') {
        Auth::requireAuth();
        $seriesSlug = $_POST['series_slug'] ?? '';
        $chapterSlug = $_POST['slug'] ?? '';
        $crud->saveChapter($seriesSlug, $chapterSlug, [
            'title' => $_POST['title'] ?? '',
            'date' => $_POST['date'] ?? date('Y-m-d'),
            'order' => (int)($_POST['order'] ?? 0),
            'tags' => array_filter(array_map('trim', explode(',', $_POST['tags'] ?? ''))),
            'status' => $_POST['status'] ?? 'draft',
            'body' => $_POST['body'] ?? '',
        ]);
        $crud->rebuild();
        header("Location: ?action=chapters&slug={$seriesSlug}");
        exit;
    } elseif ($action === 'chapter-delete') {
        Auth::requireAuth();
        $seriesSlug = $_GET['series'] ?? '';
        $chapterSlug = $_GET['slug'] ?? '';
        $crud->deleteChapter($seriesSlug, $chapterSlug);
        $crud->rebuild();
        header("Location: ?action=chapters&slug={$seriesSlug}");
        exit;
    } elseif ($action === 'rebuild') {
        Auth::requireAuth();
        $result = $crud->rebuild();
        if ($result['success']) {
            $success = 'Сборка выполнена успешно';
        } else {
            $error = 'Ошибка сборки:<br><pre>' . htmlspecialchars($result['output']) . '</pre>';
        }
    }
}

// Рендеринг
if ($action === 'login' && !Auth::check()) {
    renderLogin($error);
    exit;
}

Auth::requireAuth();

switch ($action) {
    case 'dashboard':
        renderDashboard($crud, $success, $error);
        break;
    case 'series':
        renderSeriesList($crud);
        break;
    case 'series-edit':
        renderSeriesEdit($crud, $_GET['slug'] ?? '');
        break;
    case 'chapters':
        renderChaptersList($crud, $_GET['slug'] ?? '');
        break;
    case 'chapter-edit':
        renderChapterEdit($crud, $_GET['series'] ?? '', $_GET['slug'] ?? '');
        break;
    default:
        renderDashboard($crud, $success, $error);
}

// ═══ Render Functions ═══

function renderLogin(string $error): void
{
    ?>
    <!DOCTYPE html>
    <html lang="ru">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Админ-панель | Вход</title>
        <link rel="stylesheet" href="<?= Config::SITE_URL ?>/assets/css/terminal.css">
    </head>
    <body>
        <div class="terminal" style="max-width: 400px; margin: 4rem auto;">
            <div class="terminal-header">
                <div class="buttons">
                    <span class="btn-red">●</span>
                    <span class="btn-yellow">●</span>
                    <span class="btn-green">●</span>
                </div>
                <div class="title">Вход в админ-панель</div>
            </div>
            <div class="terminal-body">
                <h1><span class="prompt">$</span> login</h1>
                <?php if ($error): ?>
                    <div class="error-message">[!] <?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                <form method="post">
                    <div class="form-group">
                        <label for="password">Пароль:</label>
                        <input type="password" name="password" id="password" required autofocus>
                    </div>
                    <button type="submit" class="btn">[ВОЙТИ]</button>
                </form>
            </div>
            <div class="terminal-footer">
                <span class="prompt">user01@srv02:~$</span>
                <span class="command">_</span>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

function renderDashboard(Crud $crud, string $success, string $error): void
{
    $series = $crud->getSeries();
    $totalSeries = count($series);
    $totalChapters = 0;
    foreach ($series as $s) {
        $totalChapters += count($crud->getChapters($s['slug']));
    }
    ?>
    <!DOCTYPE html>
    <html lang="ru">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Админ-панель | Дашборд</title>
        <link rel="stylesheet" href="<?= Config::SITE_URL ?>/assets/css/terminal.css">
        <link rel="stylesheet" href="/assets/css/admin.css">
    </head>
    <body>
        <div class="terminal">
            <?php renderAdminHeader('dashboard'); ?>
            <div class="terminal-body">
                <h1><span class="prompt">$</span> dashboard</h1>
                <?php if ($success): ?>
                    <div class="success-message">[✓] <?= $success ?></div>
                <?php endif; ?>
                <?php if ($error): ?>
                    <div class="error-message">[!] <?= $error ?></div>
                <?php endif; ?>

                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-number"><?= $totalSeries ?></div>
                        <div class="stat-label">Серий</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number"><?= $totalChapters ?></div>
                        <div class="stat-label">Глав</div>
                    </div>
                </div>

                <h2><span class="prompt">$</span> actions</h2>
                <div class="action-grid">
                    <a href="?action=series" class="btn">[СЕРИИ]</a>
                    <a href="?action=series-edit" class="btn">[+ НОВАЯ СЕРИЯ]</a>
                    <form method="post" action="?action=rebuild" style="display:inline">
                        <button type="submit" class="btn btn-warn">[ПЕРЕСОБРАТЬ]</button>
                    </form>
                    <a href="?action=logout" class="btn btn-danger">[ВЫЙТИ]</a>
                </div>
            </div>
            <?php renderAdminFooter(); ?>
        </div>
    </body>
    </html>
    <?php
}

function renderSeriesList(Crud $crud): void
{
    $series = $crud->getSeries();
    ?>
    <!DOCTYPE html>
    <html lang="ru">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Админ-панель | Серии</title>
        <link rel="stylesheet" href="<?= Config::SITE_URL ?>/assets/css/terminal.css">
    </head>
    <body>
        <div class="terminal">
            <?php renderAdminHeader('series'); ?>
            <div class="terminal-body">
                <h1><span class="prompt">$</span> ls -la ~/серии</h1>
                <a href="?action=series-edit" class="btn">[+ НОВАЯ СЕРИЯ]</a>
                <table class="terminal-table">
                    <thead>
                        <tr>
                            <th>Slug</th>
                            <th>Название</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($series as $s): ?>
                            <tr>
                                <td><?= htmlspecialchars($s['slug']) ?></td>
                                <td><?= htmlspecialchars($s['title']) ?></td>
                                <td class="action-cell">
                                    <a href="?action=series-edit&slug=<?= urlencode($s['slug']) ?>" class="btn-small">[РЕД.]</a>
                                    <a href="?action=chapters&slug=<?= urlencode($s['slug']) ?>" class="btn-small">[ГЛАВЫ]</a>
                                    <a href="?action=series-delete&slug=<?= urlencode($s['slug']) ?>" class="btn-small btn-danger" onclick="return confirm('Удалить серию?')">[×]</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($series)): ?>
                            <tr><td colspan="3" style="text-align:center;color:var(--overlay0)">Нет серий. Создайте первую!</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <?php renderAdminFooter(); ?>
        </div>
    </body>
    </html>
    <?php
}

function renderSeriesEdit(Crud $crud, string $slug): void
{
    $series = $slug ? $crud->getSeriesBySlug($slug) : null;
    $title = $series['title'] ?? '';
    $desc = $series['raw_description'] ?? '';
    $prereqs = implode("\n", $series['prerequisites'] ?? []);
    ?>
    <!DOCTYPE html>
    <html lang="ru">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Админ-панель | <?= $slug ? 'Редактировать' : 'Новая' ?> серия</title>
        <link rel="stylesheet" href="<?= Config::SITE_URL ?>/assets/css/terminal.css">
    </head>
    <body>
        <div class="terminal">
            <?php renderAdminHeader('series'); ?>
            <div class="terminal-body">
                <h1><span class="prompt">$</span> cat <?= $slug ?: 'new' ?>.md</h1>
                <form method="post" action="?action=series-save">
                    <div class="form-group">
                        <label for="slug">Slug (URL-имя, латиница):</label>
                        <input type="text" name="slug" id="slug" value="<?= htmlspecialchars($slug) ?>" <?= $slug ? 'readonly' : 'required' ?>>
                    </div>
                    <div class="form-group">
                        <label for="title">Название:</label>
                        <input type="text" name="title" id="title" value="<?= htmlspecialchars($title) ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="description">Описание (Markdown):</label>
                        <textarea name="description" id="description" rows="8"><?= htmlspecialchars($desc) ?></textarea>
                    </div>
                    <div class="form-group">
                        <label for="prerequisites">Пререквизиты (по одному на строке):</label>
                        <textarea name="prerequisites" id="prerequisites" rows="3"><?= htmlspecialchars($prereqs) ?></textarea>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn">[СОХРАНИТЬ]</button>
                        <a href="?action=series" class="btn">[ОТМЕНА]</a>
                    </div>
                </form>
            </div>
            <?php renderAdminFooter(); ?>
        </div>
    </body>
    </html>
    <?php
}

function renderChaptersList(Crud $crud, string $seriesSlug): void
{
    $series = $crud->getSeriesBySlug($seriesSlug);
    if (!$series) {
        echo "Серия не найдена";
        exit;
    }
    $chapters = $crud->getChapters($seriesSlug);
    ?>
    <!DOCTYPE html>
    <html lang="ru">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Админ-панель | Главы — <?= htmlspecialchars($series['title']) ?></title>
        <link rel="stylesheet" href="<?= Config::SITE_URL ?>/assets/css/terminal.css">
    </head>
    <body>
        <div class="terminal">
            <?php renderAdminHeader('series'); ?>
            <div class="terminal-body">
                <div class="breadcrumbs">
                    <a href="?action=series">~/серии</a> / <?= htmlspecialchars($series['title']) ?>
                </div>
                <h1><span class="prompt">$</span> ls -la ~/главы/<?= htmlspecialchars($seriesSlug) ?></h1>
                <a href="?action=chapter-edit&series=<?= urlencode($seriesSlug) ?>" class="btn">[+ НОВАЯ ГЛАВА]</a>
                <table class="terminal-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Slug</th>
                            <th>Название</th>
                            <th>Дата</th>
                            <th>Статус</th>
                            <th>Действия</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($chapters as $ch): ?>
                            <tr class="<?= $ch['status'] === 'published' ? '' : 'draft-row' ?>">
                                <td><?= $ch['order'] ?></td>
                                <td><?= htmlspecialchars($ch['slug']) ?></td>
                                <td><?= htmlspecialchars($ch['title']) ?></td>
                                <td><?= htmlspecialchars($ch['date']) ?></td>
                                <td><?= $ch['status'] === 'published' ? '✅' : '⬜' ?></td>
                                <td class="action-cell">
                                    <a href="?action=chapter-edit&series=<?= urlencode($seriesSlug) ?>&slug=<?= urlencode($ch['slug']) ?>" class="btn-small">[РЕД.]</a>
                                    <a href="?action=chapter-delete&series=<?= urlencode($seriesSlug) ?>&slug=<?= urlencode($ch['slug']) ?>" class="btn-small btn-danger" onclick="return confirm('Удалить главу?')">[×]</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (empty($chapters)): ?>
                            <tr><td colspan="6" style="text-align:center;color:var(--overlay0)">Нет глав. Создайте первую!</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <?php renderAdminFooter(); ?>
        </div>
    </body>
    </html>
    <?php
}

function renderChapterEdit(Crud $crud, string $seriesSlug, string $chapterSlug): void
{
    $series = $crud->getSeriesBySlug($seriesSlug);
    if (!$series && $seriesSlug) {
        echo "Серия не найдена";
        exit;
    }
    $chapter = $chapterSlug ? $crud->getChapter($seriesSlug, $chapterSlug) : null;
    $title = $chapter['title'] ?? '';
    $date = $chapter['date'] ?? date('Y-m-d');
    $order = $chapter['order'] ?? 0;
    $tags = implode(', ', $chapter['tags'] ?? []);
    $status = $chapter['status'] ?? 'draft';
    $body = $chapter['raw_body'] ?? '';
    ?>
    <!DOCTYPE html>
    <html lang="ru">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Админ-панель | <?= $chapterSlug ? 'Редактировать' : 'Новая' ?> главу</title>
        <link rel="stylesheet" href="<?= Config::SITE_URL ?>/assets/css/terminal.css">
        <style>
            textarea#body { min-height: 400px; font-family: var(--font-mono); font-size: 0.85rem; }
        </style>
    </head>
    <body>
        <div class="terminal">
            <?php renderAdminHeader('series'); ?>
            <div class="terminal-body">
                <div class="breadcrumbs">
                    <a href="?action=series">~/серии</a> / <a href="?action=chapters&slug=<?= urlencode($seriesSlug) ?>"><?= htmlspecialchars($series['title'] ?? '') ?></a>
                    / <?= $chapterSlug ?: 'new' ?>
                </div>
                <h1><span class="prompt">$</span> vim <?= $chapterSlug ?: 'new' ?>.md</h1>
                <form method="post" action="?action=chapter-save">
                    <input type="hidden" name="series_slug" value="<?= htmlspecialchars($seriesSlug) ?>">
                    <div class="form-group">
                        <label for="slug">Slug (URL-имя, латиница):</label>
                        <input type="text" name="slug" id="slug" value="<?= htmlspecialchars($chapterSlug) ?>" <?= $chapterSlug ? 'readonly' : 'required' ?>>
                    </div>
                    <div class="form-group">
                        <label for="title">Название:</label>
                        <input type="text" name="title" id="title" value="<?= htmlspecialchars($title) ?>" required>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="date">Дата:</label>
                            <input type="date" name="date" id="date" value="<?= htmlspecialchars($date) ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="order">Порядок:</label>
                            <input type="number" name="order" id="order" value="<?= $order ?>" min="0" required>
                        </div>
                        <div class="form-group">
                            <label for="status">Статус:</label>
                            <select name="status" id="status">
                                <option value="draft" <?= $status === 'draft' ? 'selected' : '' ?>>Черновик</option>
                                <option value="published" <?= $status === 'published' ? 'selected' : '' ?>>Опубликовано</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="tags">Теги (через запятую):</label>
                        <input type="text" name="tags" id="tags" value="<?= htmlspecialchars($tags) ?>">
                    </div>
                    <div class="form-group">
                        <label for="body">Содержание (Markdown):</label>
                        <textarea name="body" id="body" rows="20"><?= htmlspecialchars($body) ?></textarea>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn">[СОХРАНИТЬ]</button>
                        <a href="?action=chapters&slug=<?= urlencode($seriesSlug) ?>" class="btn">[ОТМЕНА]</a>
                    </div>
                </form>
            </div>
            <?php renderAdminFooter(); ?>
        </div>
    </body>
    </html>
    <?php
}

function renderAdminHeader(string $active): void
{
    ?>
    <div class="terminal-header">
        <div class="buttons">
            <span class="btn-red">●</span>
            <span class="btn-yellow">●</span>
            <span class="btn-green">●</span>
        </div>
        <div class="title">Админ-панель</div>
    </div>
    <nav class="terminal-nav">
        <a href="?action=dashboard" class="<?= $active === 'dashboard' ? 'active' : '' ?>">~/дашборд</a>
        <a href="?action=series" class="<?= $active === 'series' ? 'active' : '' ?>">~/серии</a>
        <a href="?action=logout">~/выйти</a>
    </nav>
    <?php
}

function renderAdminFooter(): void
{
    ?>
    <div class="terminal-footer">
        <span class="prompt">admin@srv02:~$</span>
        <span class="command">_</span>
        <span class="footer-info"><?= date('Y-m-d H:i') ?> <span class="blink-cursor">█</span></span>
    </div>
    </div>
    <?php
}
