<section class="hero">
    <div class="hero-content">
        <span class="badge badge-live">Sezon <?= e($settings['season'] ?? '') ?></span>
        <h1><?= e($settings['tournament_name'] ?? 'Trofeu Hub') ?></h1>
        <p>Urmărește clasamentul, meciurile live și statisticile turneului.</p>
    </div>
    <div class="stats-grid">
        <div class="stat-card">
            <span class="stat-value"><?= (int) ($stats['numTeams'] ?? 0) ?></span>
            <span class="stat-label">Echipe</span>
        </div>
        <div class="stat-card stat-card-players">
            <span class="stat-value"><?= (int) ($stats['numPlayers'] ?? 0) ?></span>
            <span class="stat-label">Jucători</span>
        </div>
        <div class="stat-card">
            <span class="stat-value"><?= (int) ($stats['numMatches'] ?? 0) ?></span>
            <span class="stat-label">Meciuri jucate</span>
        </div>
        <div class="stat-card">
            <span class="stat-value"><?= (int) ($stats['totalGoals'] ?? 0) ?></span>
            <span class="stat-label">Goluri</span>
        </div>
    </div>
</section>

<div class="home-stats-bar">
    <span><strong><?= (int) ($stats['numTeams'] ?? 0) ?></strong> echipe</span>
    <span class="home-stats-sep">·</span>
    <span><strong><?= (int) ($stats['numPlayers'] ?? 0) ?></strong> jucători înscriși</span>
    <span class="home-stats-sep">·</span>
    <span><strong><?= (int) ($stats['totalGoals'] ?? 0) ?></strong> goluri marcate</span>
</div>

<?php if (!empty($liveMatches)): ?>
<section class="section section-live">
    <div class="section-header">
        <h2><span class="live-dot"></span> Meciuri live</h2>
        <a href="<?= url('/meciuri') ?>" class="link-more">Toate meciurile →</a>
    </div>
    <div class="matches-list matches-list-home">
        <?php foreach ($liveMatches as $m): ?>
            <?php include __DIR__ . '/../partials/match_card.php'; ?>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<section class="section">
    <div class="section-header">
        <h2>Top clasament</h2>
        <a href="<?= url('/clasament') ?>" class="link-more">Vezi tot →</a>
    </div>
    <?php include __DIR__ . '/../partials/leaderboard_table.php'; ?>
</section>

<?php if (!empty($upcoming)): ?>
<section class="section">
    <div class="section-header">
        <h2>Următoarele meciuri</h2>
        <a href="<?= url('/meciuri') ?>" class="link-more">Program complet →</a>
    </div>
    <div class="matches-list matches-list-home">
        <?php foreach ($upcoming as $m): ?>
            <?php include __DIR__ . '/../partials/match_card.php'; ?>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>
