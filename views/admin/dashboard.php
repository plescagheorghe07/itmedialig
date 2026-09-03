<div class="stats-grid admin-stats">
    <div class="stat-card"><span class="stat-value"><?= (int) $stats['numTeams'] ?></span><span class="stat-label">Echipe</span></div>
    <div class="stat-card"><span class="stat-value"><?= (int) $stats['numMatches'] ?></span><span class="stat-label">Meciuri</span></div>
    <div class="stat-card"><span class="stat-value"><?= (int) $stats['totalGoals'] ?></span><span class="stat-label">Goluri</span></div>
</div>
<div class="card">
    <h2>Bun venit!</h2>
    <p>Folosește meniul din stânga pentru a administra turneul.</p>
    <div class="quick-actions" style="margin-top:1rem;display:flex;gap:.75rem;flex-wrap:wrap">
        <a href="<?= url('/admin/sezoane') ?>" class="btn btn-secondary">📦 Arhivează sezon</a>
        <a href="<?= url('/admin/exporturi') ?>" class="btn btn-secondary">📄 Export PDF</a>
    </div>
</div>
