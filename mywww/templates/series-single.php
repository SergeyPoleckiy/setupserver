<div class="series-single-section">
    <div class="breadcrumbs">
        <a href="<?= $this->e($siteUrl) ?>/series/">~/серии</a> / <?= $this->e($series['title']) ?>
    </div>

    <h1><span class="prompt">$</span> cat <?= $this->e($series['slug']) ?>.md</h1>
    <div class="markdown-body">
        <?= $series['description'] ?>
    </div>

    <?php if (!empty($series['prerequisites'])): ?>
        <div class="prerequisites-block">
            <h3><span class="prompt">#</span> Пререквизиты:</h3>
            <ul>
                <?php foreach ($series['prerequisites'] as $prereq): ?>
                    <li><?= $this->e($prereq) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <h2><span class="prompt">$</span> ls -la ~/серии/<?= $this->e($series['slug']) ?></h2>
    
    <div class="progress-container-large">
        <?php 
        $percent = $progress['total'] > 0 
            ? round(($progress['published'] / $progress['total']) * 100) 
            : 0;
        ?>
        <div class="progress-bar-wrapper">
            <div class="progress-bar" style="width: <?= $percent ?>%"></div>
        </div>
        <span class="progress-text-large">Завершено на <?= $percent ?>% (<?= $progress['published'] ?> из <?= $progress['total'] ?> глав опубликовано)</span>
    </div>

    <ol class="chapters-list">
        <?php foreach ($chapters as $chapter): ?>
            <li class="chapter-item <?= $chapter['status'] === 'published' ? 'published' : 'draft' ?>">
                <span class="chapter-status-indicator">[<?= $chapter['status'] === 'published' ? 'OK' : '..' ?>]</span>
                <?php if ($chapter['status'] === 'published'): ?>
                    <a href="<?= $this->e($siteUrl) ?>/<?= $this->e($series['slug']) ?>/<?= $this->e($chapter['slug']) ?>/"><?= $this->e($chapter['title']) ?></a>
                <?php else: ?>
                    <span class="draft-title"><?= $this->e($chapter['title']) ?> (в разработке)</span>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ol>
</div>
