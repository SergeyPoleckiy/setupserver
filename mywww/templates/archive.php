<div class="archive-section">
    <h1><span class="prompt">$</span> ls -la ~/архив_записей</h1>
    <p>Полный архив всех опубликованных глав, сгруппированных по месяцам.</p>

    <?php
    $groups = [];
    foreach ($chapters as $ch) {
        $month = date('Y-m', strtotime($ch['date']));
        $groups[$month][] = $ch;
    }
    krsort($groups);
    ?>

    <?php foreach ($groups as $month => $monthChapters): ?>
        <?php
        $monthName = strftime('%B %Y', strtotime($month . '-01'));
        setlocale(LC_TIME, 'ru_RU.UTF-8');
        $monthName = strftime('%B %Y', strtotime($month . '-01'));
        ?>
        <div class="archive-month">
            <h2 class="month-header">
                <span class="prompt">$</span> ls -la ~/архив/<?= $month ?>/
            </h2>
            <ul class="archive-list">
                <?php foreach ($monthChapters as $ch): ?>
                    <li class="archive-item">
                        <span class="archive-date">[<?= date('Y-m-d', strtotime($ch['date'])) ?>]</span>
                        <span class="archive-series">[<?= $this->e($ch['series']) ?>]</span>
                        <a href="<?= $this->e($siteUrl) ?>/<?= $this->e($ch['series']) ?>/<?= $this->e($ch['slug']) ?>/">
                            <?= $this->e($ch['title']) ?>
                        </a>
                        <?php if (!empty($ch['tags'])): ?>
                            <span class="archive-tags">
                                <?php foreach ($ch['tags'] as $tag): ?>
                                    <span class="tag">#<?= $this->e($tag) ?></span>
                                <?php endforeach; ?>
                            </span>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endforeach; ?>

    <?php if (empty($groups)): ?>
        <div class="empty-archive">
            <p>[!] Архив пуст. Ещё не опубликовано ни одной главы.</p>
        </div>
    <?php endif; ?>
</div>
