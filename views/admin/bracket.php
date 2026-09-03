<?php $tree = $bracketTree; $size = $tree['size']; ?>
<div class="bracket-admin-page">
    <div class="bracket-admin-toolbar card">
        <form method="get" action="<?= url('/admin/bracket') ?>" class="bracket-size-form">
            <label>Număr echipe în bracket
                <select name="size" id="bracket-size-select" data-current="<?= (int) ($selectedSize ?? $size) ?>">
                    <?php foreach ($bracketSizes as $s): ?>
                        <option value="<?= $s ?>" <?= ($selectedSize ?? $size) === $s ? 'selected' : '' ?>><?= $s ?> echipe</option>
                    <?php endforeach; ?>
                </select>
            </label>
            <a href="<?= url('/admin/bracket') ?>?size=<?= (int) ($selectedSize ?? $size) ?>&init=1" class="btn btn-secondary" onclick="return confirm('Resetezi structura bracket la <?= (int) ($selectedSize ?? $size) ?> echipe?')">Regenerează structura</a>
            <a href="<?= url('/bracket') ?>" target="_blank" class="btn btn-ghost">Previzualizare site</a>
        </form>
        <p class="hint text-muted">Alege numărul de echipe, generează structura, apoi completează meciurile. Câștigătorul avansează automat când setezi scorurile.</p>
    </div>

    <form method="post" action="<?= url('/admin/bracket') ?>" id="bracket-admin-form">
        <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
        <input type="hidden" name="bracket_size" value="<?= (int) $size ?>">

        <div class="bracket-editor" id="bracket-editor">
            <?php foreach ($tree['rounds'] as $round): ?>
            <div class="bracket-editor-round" data-round="<?= $round['index'] ?>">
                <h3 class="bracket-round-title"><?= e($round['label']) ?></h3>
                <div class="bracket-editor-matches">
                    <?php foreach ($round['matches'] as $match):
                        $r = $round['index'];
                        $mi = $match['match_index'];
                    ?>
                    <div class="bracket-match-card" data-round="<?= $r ?>" data-match="<?= $mi ?>">
                        <div class="bracket-match-header">Meci <?= $mi + 1 ?></div>
                        <div class="bracket-team-row">
                            <select name="matches[<?= $r ?>][<?= $mi ?>][team1_id]" class="bracket-team-select" data-slot="team1">
                                <option value="">— TBD —</option>
                                <?php foreach ($teams as $t): ?>
                                <option value="<?= e($t['id']) ?>" <?= ($match['team1_id'] ?? '') === $t['id'] ? 'selected' : '' ?>><?= e($t['nume']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <input type="number" name="matches[<?= $r ?>][<?= $mi ?>][score1]" class="bracket-score-input" min="0" placeholder="0" value="<?= e($match['score1'] ?? '') ?>">
                        </div>
                        <div class="bracket-vs">vs</div>
                        <div class="bracket-team-row">
                            <select name="matches[<?= $r ?>][<?= $mi ?>][team2_id]" class="bracket-team-select" data-slot="team2">
                                <option value="">— TBD —</option>
                                <?php foreach ($teams as $t): ?>
                                <option value="<?= e($t['id']) ?>" <?= ($match['team2_id'] ?? '') === $t['id'] ? 'selected' : '' ?>><?= e($t['nume']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <input type="number" name="matches[<?= $r ?>][<?= $mi ?>][score2]" class="bracket-score-input" min="0" placeholder="0" value="<?= e($match['score2'] ?? '') ?>">
                        </div>
                        <?php if ($match['winner_id']): ?>
                        <div class="bracket-winner-hint">→ Câștigător setat</div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="bracket-admin-footer">
            <button type="submit" class="btn btn-primary btn-lg">Salvează bracket</button>
        </div>
    </form>
</div>
<script src="<?= asset('js/bracket-admin.js') ?>"></script>
