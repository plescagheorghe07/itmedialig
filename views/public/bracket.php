<?php
$tree = $bracketTree;
$bannerTitle = 'Bracket eliminatoriu';
$bannerSubtitle = 'Faza eliminatorie · ' . (int) $tree['size'] . ' echipe';
$bannerBadge = 'Playoff';
include __DIR__ . '/../partials/page_banner.php';

$finalWinner = null;
if (!empty($tree['rounds'])) {
    $lastRound = end($tree['rounds']);
    $finalMatch = $lastRound['matches'][0] ?? null;
    if ($finalMatch && $finalMatch['winner_id']) {
        $finalWinner = $finalMatch['winner_id'] === $finalMatch['team1_id']
            ? ['name' => $finalMatch['team1_nume'], 'logo' => $finalMatch['team1_logo']]
            : ['name' => $finalMatch['team2_nume'], 'logo' => $finalMatch['team2_logo']];
    }
}
?>

<?php if (empty($tree['rounds'])): ?>
<div class="empty-state card">
    <div class="empty-state-icon">🏆</div>
    <h3>Bracket neconfigurat</h3>
    <p class="text-muted">Faza eliminatorie va fi afișată aici după configurarea din panoul admin.</p>
</div>
<?php else: ?>
<div class="bracket-arena card">
    <div class="bracket-pro-scroll">
        <div class="bracket-pro">
            <?php foreach ($tree['rounds'] as $round): ?>
            <div class="bracket-pro-round" data-round="<?= (int) $round['index'] ?>">
                <div class="bracket-pro-round-head">
                    <span class="bracket-pro-round-num">R<?= $round['index'] + 1 ?></span>
                    <span><?= e($round['label']) ?></span>
                </div>
                <div class="bracket-pro-slots">
                    <?php foreach ($round['matches'] as $match): ?>
                    <div class="bracket-pro-match">
                        <?php
                        $teams = [
                            ['id' => $match['team1_id'], 'name' => $match['team1_nume'], 'logo' => $match['team1_logo'], 'score' => $match['score1']],
                            ['id' => $match['team2_id'], 'name' => $match['team2_nume'], 'logo' => $match['team2_logo'], 'score' => $match['score2']],
                        ];
                        foreach ($teams as $t):
                            $isWinner = $match['winner_id'] && $t['id'] && $match['winner_id'] === $t['id'];
                            $isLoser = $match['winner_id'] && $t['id'] && $match['winner_id'] !== $t['id'];
                        ?>
                        <div class="bracket-pro-team <?= $isWinner ? 'is-winner' : '' ?> <?= $isLoser ? 'is-loser' : '' ?> <?= !$t['id'] ? 'is-tbd' : '' ?>">
                            <?php if ($t['id']): ?>
                                <img src="<?= upload_url($t['logo'], 'team') ?>" alt="" class="team-logo-xs">
                                <span class="bracket-pro-name"><?= e($t['name']) ?></span>
                                <?php if ($t['score'] !== null): ?>
                                    <span class="bracket-pro-score"><?= (int) $t['score'] ?></span>
                                <?php endif; ?>
                                <?php if ($isWinner): ?><span class="bracket-pro-crown" title="Câștigător">👑</span><?php endif; ?>
                            <?php else: ?>
                                <span class="bracket-pro-tbd">De stabilit</span>
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endforeach; ?>

            <?php if ($finalWinner): ?>
            <div class="bracket-pro-round bracket-pro-champion">
                <div class="bracket-pro-round-head">Campioană</div>
                <div class="bracket-champion-card">
                    <span class="bracket-champion-trophy">🏆</span>
                    <img src="<?= upload_url($finalWinner['logo'], 'team') ?>" alt="" class="team-logo">
                    <strong><?= e($finalWinner['name']) ?></strong>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php endif; ?>
