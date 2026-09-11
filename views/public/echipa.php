<div class="team-profile card">
    <div class="team-profile-header">
        <div class="team-profile-logo">
            <img src="<?= upload_url($team['logo_path'], 'team') ?>" alt="" class="team-logo-lg">
        </div>
        <div class="team-profile-info">
            <span class="page-banner-badge"><?= e($team['grupa']) ?></span>
            <h1><?= e($team['nume']) ?></h1>
            <p class="text-muted"><?= count($players) ?> jucători în lot</p>
            <div class="team-record">
                <span><strong><?= (int) $record['w'] ?></strong> V</span>
                <span><strong><?= (int) $record['d'] ?></strong> E</span>
                <span><strong><?= (int) $record['l'] ?></strong> Î</span>
                <span class="text-muted"><?= (int) $record['gf'] ?>:<?= (int) $record['ga'] ?></span>
            </div>
        </div>
    </div>
</div>

<section class="section">
    <div class="section-header"><h2>Lot jucători</h2></div>
    <div class="players-grid">
        <?php foreach ($players as $p): ?>
            <button type="button" class="player-card card player-card-btn"
                data-player-open
                data-player='<?= e(json_encode([
                    'id' => $p['id'],
                    'name' => $p['prenume'] . ' ' . $p['nume'],
                    'photo' => upload_url($p['poza_path'], 'player'),
                    'jersey' => $p['numar_tricou'],
                    'position' => $p['pozitie'],
                    'goals' => (int) $p['goals'],
                    'motm' => (int) $p['man_of_the_match'],
                    'goals_detail' => array_map(static fn($g) => [
                        'minute' => $g['minute'],
                        'vs' => ($g['echipa1_nume'] ?? '') . ' vs ' . ($g['echipa2_nume'] ?? ''),
                        'date' => !empty($g['data_meci']) ? date('d.m.Y', strtotime($g['data_meci'])) : '',
                    ], $p['goals_detail'] ?? []),
                ], JSON_UNESCAPED_UNICODE)) ?>'>
                <img src="<?= upload_url($p['poza_path'], 'player') ?>" alt="" class="player-photo-lg">
                <div class="player-card-info">
                    <strong><?= e($p['prenume'] . ' ' . $p['nume']) ?></strong>
                    <?php if ($p['numar_tricou']): ?><span class="jersey-badge">#<?= (int) $p['numar_tricou'] ?></span><?php endif; ?>
                    <?php if ($p['pozitie']): ?><small class="text-muted"><?= e($p['pozitie']) ?></small><?php endif; ?>
                    <div class="player-mini-stats">
                        <span><?= (int) $p['goals'] ?> goluri</span>
                        <?php if ($p['man_of_the_match'] > 0): ?>
                            <span class="player-motm-tag"><?= icon('star', 'icon icon-sm') ?> OM ×<?= (int) $p['man_of_the_match'] ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            </button>
        <?php endforeach; ?>
        <?php if (empty($players)): ?>
            <p class="text-muted">Niciun jucător în lot.</p>
        <?php endif; ?>
    </div>
</section>

<section class="section">
    <div class="section-header">
        <h2>Istoric meciuri</h2>
        <span class="text-muted"><?= count($matches) ?> meciuri</span>
    </div>
    <div class="matches-list">
        <?php foreach ($matches as $m): ?>
            <?php include __DIR__ . '/../partials/match_card.php'; ?>
        <?php endforeach; ?>
        <?php if (empty($matches)): ?>
            <div class="card empty-state"><p class="text-muted">Niciun meci pentru această echipă.</p></div>
        <?php endif; ?>
    </div>
</section>

<dialog id="playerOverlay" class="player-overlay" aria-label="Detalii jucător">
    <div class="player-overlay-card">
        <button type="button" class="player-overlay-close" data-player-close aria-label="Închide">&times;</button>
        <img src="" alt="" class="player-overlay-photo" id="po-photo">
        <h3 id="po-name"></h3>
        <div class="player-overlay-meta">
            <span id="po-jersey" class="jersey-badge" hidden></span>
            <small id="po-position" class="text-muted"></small>
        </div>
        <div class="player-overlay-stats">
            <div><strong id="po-goals">0</strong><span>Goluri</span></div>
            <div><strong id="po-motm">0</strong><span>Omul meciului</span></div>
        </div>
        <div class="player-overlay-goals">
            <h4>Goluri marcate</h4>
            <ul id="po-goals-list"></ul>
            <p id="po-goals-empty" class="text-muted" hidden>Niciun gol înregistrat.</p>
        </div>
    </div>
</dialog>
<script>
(function () {
    const dlg = document.getElementById('playerOverlay');
    if (!dlg) return;
    const photo = document.getElementById('po-photo');
    const name = document.getElementById('po-name');
    const jersey = document.getElementById('po-jersey');
    const position = document.getElementById('po-position');
    const goals = document.getElementById('po-goals');
    const motm = document.getElementById('po-motm');
    const list = document.getElementById('po-goals-list');
    const empty = document.getElementById('po-goals-empty');

    document.querySelectorAll('[data-player-open]').forEach((btn) => {
        btn.addEventListener('click', () => {
            let data;
            try { data = JSON.parse(btn.getAttribute('data-player') || '{}'); }
            catch (e) { return; }
            photo.src = data.photo || '';
            name.textContent = data.name || '';
            if (data.jersey) {
                jersey.hidden = false;
                jersey.textContent = '#' + data.jersey;
            } else {
                jersey.hidden = true;
            }
            position.textContent = data.position || '';
            goals.textContent = data.goals || 0;
            motm.textContent = data.motm || 0;
            list.innerHTML = '';
            const details = data.goals_detail || [];
            empty.hidden = details.length > 0;
            details.forEach((g) => {
                const li = document.createElement('li');
                li.textContent = (g.minute != null ? g.minute + "'" : '—') + ' · ' + (g.vs || '') + (g.date ? ' · ' + g.date : '');
                list.appendChild(li);
            });
            dlg.showModal();
        });
    });
    dlg.querySelectorAll('[data-player-close]').forEach((b) => b.addEventListener('click', () => dlg.close()));
    dlg.addEventListener('click', (e) => { if (e.target === dlg) dlg.close(); });
})();
</script>
