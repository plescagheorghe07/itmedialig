<div class="admin-dash-hero card">
    <div>
        <p class="admin-eyebrow">Prezentare generală</p>
        <h2>Panoul turneului</h2>
        <p class="text-muted">Gestionează echipe, meciuri live, bracket și arhive dintr-un singur loc.</p>
    </div>
    <div class="admin-dash-actions">
        <a href="<?= url('/admin/meciuri') ?>" class="btn btn-primary"><?= icon('ball', 'icon icon-sm') ?> Meciuri live</a>
        <a href="<?= url('/admin/echipe') ?>" class="btn btn-secondary"><?= icon('shield', 'icon icon-sm') ?> Echipe</a>
    </div>
</div>

<div class="stats-grid admin-stats">
    <div class="stat-card"><span class="stat-value"><?= (int) ($stats['numTeams'] ?? 0) ?></span><span class="stat-label">Echipe</span></div>
    <div class="stat-card"><span class="stat-value"><?= (int) ($stats['numPlayers'] ?? 0) ?></span><span class="stat-label">Jucători</span></div>
    <div class="stat-card"><span class="stat-value"><?= (int) ($stats['numMatches'] ?? 0) ?></span><span class="stat-label">Meciuri</span></div>
    <div class="stat-card"><span class="stat-value"><?= (int) ($stats['totalGoals'] ?? 0) ?></span><span class="stat-label">Goluri</span></div>
</div>

<div class="admin-quick-grid">
    <a href="<?= url('/admin/sezoane') ?>" class="admin-quick-card card">
        <span class="admin-quick-ico"><?= icon('archive', 'icon icon-lg') ?></span>
        <strong>Arhivează sezon</strong>
        <p>Salvează clasamentul și statisticile sezonului curent.</p>
    </a>
    <a href="<?= url('/admin/exporturi') ?>" class="admin-quick-card card">
        <span class="admin-quick-ico"><?= icon('file', 'icon icon-lg') ?></span>
        <strong>Export PDF</strong>
        <p>Generează clasament tipărit pentru afișare sau arhivă.</p>
    </a>
    <a href="<?= url('/admin/bracket') ?>" class="admin-quick-card card">
        <span class="admin-quick-ico"><?= icon('tree', 'icon icon-lg') ?></span>
        <strong>Bracket</strong>
        <p>Configurează faza eliminatorie vizual.</p>
    </a>
    <a href="<?= url('/admin/setari') ?>" class="admin-quick-card card">
        <span class="admin-quick-ico"><?= icon('settings', 'icon icon-lg') ?></span>
        <strong>Setări</strong>
        <p>Nume turneu, Redis, port WebSocket.</p>
    </a>
</div>
