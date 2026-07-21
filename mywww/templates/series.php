<div class="library-section">
    <h1><span class="prompt">$</span> tree -L 1 ~/серии_руководств</h1>
    <p>Каталог доступных серий руководств. Каждая серия — это законченный пошаговый цикл статей, ведущий от чистой системы к готовому рабочему решению.</p>

    <table class="terminal-table">
        <thead>
            <tr>
                <th>Название серии</th>
                <th>Главы</th>
                <th>Прогресс</th>
                <th>Перейти</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($series as $item): ?>
                <?php 
                $percent = $item['progress']['total'] > 0 
                    ? round(($item['progress']['published'] / $item['progress']['total']) * 100) 
                    : 0;
                ?>
                <tr>
                    <td><a href="<?= $this->e($siteUrl) ?>/<?= $this->e($item['slug']) ?>/"><?= $this->e($item['title']) ?></a></td>
                    <td><?= $item['progress']['published'] ?> / <?= $item['progress']['total'] ?></td>
                    <td>
                        <div class="table-progress">
                            <span class="progress-bar-wrapper" style="width: 100px; display: inline-block; margin-right: 10px;">
                                <span class="progress-bar" style="width: <?= $percent ?>%"></span>
                            </span>
                            <span><?= $percent ?>%</span>
                        </div>
                    </td>
                    <td><a href="<?= $this->e($siteUrl) ?>/<?= $this->e($item['slug']) ?>/" class="term-link">[ОТКРЫТЬ]</a></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
