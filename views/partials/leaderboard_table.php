<table class="leaderboard-table">
    <thead>
        <tr>
            <th>#</th>
            <th>Echipă</th>
            <th>MJ</th>
            <th>V</th>
            <th>E</th>
            <th>Î</th>
            <th>G+</th>
            <th>G-</th>
            <th>Gd</th>
            <th>Pts</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($leaderboard as $i => $row): ?>
            <tr>
                <td class="rank"><?= $i + 1 ?></td>
                <td class="team-cell text-left">
                    <div class="team-cell-inner">
                        <img src="<?= upload_url($row['logo_url'] ?? null, 'team') ?>" alt="" class="team-logo-xs">
                        <div>
                            <a href="<?= url('/echipa/' . $row['id']) ?>"><?= e($row['nume']) ?></a>
                            <small class="text-muted"><?= e($row['grupa']) ?></small>
                        </div>
                    </div>
                </td>
                <td><?= (int) $row['matches_played'] ?></td>
                <td><?= (int) $row['victories'] ?></td>
                <td><?= (int) $row['draws'] ?></td>
                <td><?= (int) $row['losses'] ?></td>
                <td><?= (int) $row['goals_scored'] ?></td>
                <td><?= (int) $row['goals_conceded'] ?></td>
                <td><?= (int) $row['goal_difference'] ?></td>
                <td><strong><?= (int) $row['points'] ?></strong></td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($leaderboard)): ?>
            <tr><td colspan="10" class="text-muted">Nicio echipă în clasament.</td></tr>
        <?php endif; ?>
    </tbody>
</table>
