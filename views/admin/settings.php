<div class="card">
    <form method="post" action="<?= url('/admin/setari') ?>" class="settings-form">
        <input type="hidden" name="_csrf" value="<?= e($csrf) ?>">
        <h2>Turneu</h2>
        <label>Nume turneu <input type="text" name="tournament_name" value="<?= e($settings['tournament_name'] ?? '') ?>" required></label>
        <label>Sezon <input type="text" name="season" value="<?= e($settings['season'] ?? '') ?>"></label>
        <div class="form-row">
            <label>Puncte victorie <input type="number" name="points_win" value="<?= e($settings['points_win'] ?? '3') ?>" min="0"></label>
            <label>Puncte egal <input type="number" name="points_draw" value="<?= e($settings['points_draw'] ?? '1') ?>" min="0"></label>
        </div>

        <h2 style="margin-top:1.5rem">Redis & Live</h2>
        <label class="checkbox-label">
            <input type="checkbox" name="redis_enabled" <?= ($settings['redis_enabled'] ?? '0') === '1' ? 'checked' : '' ?>>
            Activează cache Redis pentru clasament
        </label>
        <p class="hint">Configurează host/port Redis în <code>config.php</code> (secțiunea redis).</p>
        <label>Port WebSocket scor live
            <input type="number" name="ws_port" value="<?= e($settings['ws_port'] ?? config('websocket.port', 8080)) ?>" min="1024" max="65535">
        </label>
        <p class="hint">Pornește serverul: <code>php bin/websocket-server.php</code></p>

        <button type="submit" class="btn btn-primary" style="margin-top:1rem">Salvează setări</button>
    </form>
</div>
