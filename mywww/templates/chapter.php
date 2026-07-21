<div class="chapter-section">
    <div class="breadcrumbs">
        <a href="<?= $this->e($siteUrl) ?>/series/">~/серии</a>
        / <a href="<?= $this->e($siteUrl) ?>/<?= $this->e($series['slug']) ?>/"><?= $this->e($series['title']) ?></a>
        / <?= $this->e($chapter['title']) ?>
    </div>

    <h1><span class="prompt">$</span> cat <?= $this->e($chapter['slug']) ?>.md</h1>

    <div class="chapter-meta">
        <span class="meta-date">📅 <?= date('Y-m-d', strtotime($chapter['date'])) ?></span>
        <?php if (!empty($chapter['tags'])): ?>
            <span class="meta-tags">
                🏷️ <?php foreach ($chapter['tags'] as $tag): ?>
                    <span class="tag"><?= $this->e($tag) ?></span>
                <?php endforeach; ?>
            </span>
        <?php endif; ?>
    </div>

    <div class="markdown-body chapter-body">
        <?= $chapter['body'] ?>
    </div>

    <div class="chapter-navigation">
        <?php if ($prevChapter): ?>
            <a class="nav-prev" href="<?= $this->e($siteUrl) ?>/<?= $this->e($prevChapter['series']) ?>/<?= $this->e($prevChapter['slug']) ?>/">
                ← <?= $this->e($prevChapter['title']) ?>
            </a>
        <?php else: ?>
            <span class="nav-prev disabled">← начало серии</span>
        <?php endif; ?>

        <span class="nav-center"><?= $this->e($chapter['title']) ?></span>

        <?php if ($nextChapter): ?>
            <a class="nav-next" href="<?= $this->e($siteUrl) ?>/<?= $this->e($nextChapter['series']) ?>/<?= $this->e($nextChapter['slug']) ?>/">
                <?= $this->e($nextChapter['title']) ?> →
            </a>
        <?php else: ?>
            <span class="nav-next disabled">конец серии →</span>
        <?php endif; ?>
    </div>

    <div class="comments-section">
        <h2><span class="prompt">$</span> cat comments.txt</h2>
        <div class="comments-placeholder">
            <p class="comment-disabled">[!] Комментарии временно отключены. Ведутся технические работы по настройке SMTP-транспорта.</p>
            <p class="comment-email">✉️ По вопросам и замечаниям: <span class="email-placeholder">root@srv02.poleckiy.ru</span></p>
        </div>
    </div>
</div>
