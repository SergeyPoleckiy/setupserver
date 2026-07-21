<div class="welcome-section">
    <h1><span class="prompt">$</span> cat welcome_message.txt</h1>
    <p>Добро пожаловать в личный блог srv02. Здесь собраны пошаговые руководства по системному администрированию, настройке Linux-серверов и разработке программного обеспечения.</p>
</div>

<div class="current-reading">
    <h2><span class="prompt">$</span> ls -la ~/серии_в_процессе</h2>
    <div class="series-list">
        <?php foreach ($series as $item): ?>
            <div class="series-card">
                <h3><a href="<?= $this->e($siteUrl) ?>/<?= $this->e($item['slug']) ?>/"><?= $this->e($item['title']) ?></a></h3>
                <div class="progress-container">
                    <div class="progress-bar-wrapper">
                        <div class="progress-bar" style="width: <?= $item['progress']['total'] > 0 ? ($item['progress']['published'] / $item['progress']['total'] * 100) : 0 ?>%"></div>
                    </div>
                    <span class="progress-text"><?= $item['progress']['published'] ?> из <?= $item['progress']['total'] ?> глав опубликовано</span>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<div class="new-chapters">
    <h2><span class="prompt">$</span> tail -n 5 ~/новые_главы.log</h2>
    <ul class="chapter-logs">
        <?php foreach ($newChapters as $chapter): ?>
            <li>
                <span class="log-date">[<?= date('Y-m-d', strtotime($chapter['date'])) ?>]</span>
                <span class="log-series">[<?= $this->e($chapter['series']) ?>]</span>
                <a href="<?= $this->e($siteUrl) ?>/<?= $this->e($chapter['series']) ?>/<?= $this->e($chapter['slug']) ?>/"><?= $this->e($chapter['title']) ?></a>
            </li>
        <?php endforeach; ?>
    </ul>
</div>
