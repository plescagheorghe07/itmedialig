/**
 * Trofeu Hub — scoruri live via WebSocket (polling doar ca fallback)
 */
(function () {
    const statusLabels = { se_joaca: '● LIVE', terminat: 'Terminat', programat: 'Programat' };
    const LIVE_TYPES = new Set([
        'match_update', 'goal_added', 'goal_removed',
        'match_started', 'match_finished', 'motm_updated', 'connected'
    ]);

    function updateMatchRow(data) {
        if (!data || !data.id) return;
        const row = document.querySelector('[data-match-id="' + data.id + '"]');
        if (!row) return;

        row.className = row.className.replace(/\bstatus-\S+/g, '').trim();
        row.classList.add('match-row', 'match-card-pro', 'status-' + data.status);
        if (data.status === 'se_joaca') row.classList.add('is-live');

        const scoreEl = row.querySelector('[data-live-score]');
        const statusEl = row.querySelector('[data-live-status]');
        const liveLink = row.querySelector('[data-live-link]');
        const watchBtn = row.querySelector('[data-watch-btn]');

        if (scoreEl) {
            if (data.status === 'programat') {
                scoreEl.innerHTML = '<span class="vs">vs</span>';
            } else {
                const s1 = data.scor_echipa1 ?? 0;
                const s2 = data.scor_echipa2 ?? 0;
                scoreEl.innerHTML = '<strong class="score-num">' + s1 + ' : ' + s2 + '</strong>';
            }
        }
        if (statusEl) {
            statusEl.className = 'status-badge status-' + data.status;
            statusEl.textContent = statusLabels[data.status] || data.status;
        }
        if (liveLink) {
            liveLink.style.display = data.status === 'se_joaca' && data.live_link ? '' : 'none';
        }
        if (watchBtn) {
            watchBtn.style.display = (data.status === 'se_joaca' || data.status === 'terminat') ? '' : 'none';
            if (data.status === 'se_joaca') {
                watchBtn.className = 'btn btn-sm btn-live';
                watchBtn.innerHTML = '<span class="live-dot"></span> Vizionează live';
            } else if (data.status === 'terminat') {
                watchBtn.className = 'btn btn-sm btn-secondary';
                watchBtn.textContent = 'Detalii meci';
            }
        }

        const card = document.querySelector('.match-card[data-match-id="' + data.id + '"]');
        if (card) {
            card.className = 'match-card status-' + data.status;
            const cardScore = card.querySelector('.score');
            if (cardScore) {
                cardScore.textContent = data.status === 'programat' ? 'vs' : ((data.scor_echipa1 ?? 0) + ' : ' + (data.scor_echipa2 ?? 0));
            }
        }
    }

    function handleMessage(msg) {
        if (!msg || !LIVE_TYPES.has(msg.type)) return;
        const data = msg.data || msg.match || msg;
        if (data && data.id) updateMatchRow(data);
    }

    let ws = null;
    let wsConnected = false;
    let pollTimer = null;
    const pollUrl = document.querySelector('meta[name="trofeu-api-live"]')?.content;

    function startPolling() {
        if (pollTimer || !pollUrl) return;
        pollTimer = setInterval(async function () {
            if (wsConnected) return;
            try {
                const res = await fetch(pollUrl, { cache: 'no-store' });
                const json = await res.json();
                if (json.success && json.data) json.data.forEach(updateMatchRow);
            } catch (e) {}
        }, 30000);
    }

    function stopPolling() {
        if (pollTimer) { clearInterval(pollTimer); pollTimer = null; }
    }

    function connectWs(url) {
        try { ws = new WebSocket(url); } catch (e) { startPolling(); return; }

        ws.onopen = function () {
            wsConnected = true;
            stopPolling();
            ws.send(JSON.stringify({ type: 'ping' }));
            document.body.classList.add('ws-connected');
        };

        ws.onmessage = function (ev) {
            try {
                const msg = JSON.parse(ev.data);
                if (msg.type === 'pong') return;
                handleMessage(msg);
            } catch (e) {}
        };

        ws.onclose = function () {
            wsConnected = false;
            document.body.classList.remove('ws-connected');
            startPolling();
            setTimeout(function () { connectWs(url); }, 2000);
        };

        ws.onerror = function () {
            wsConnected = false;
            startPolling();
        };
    }

    const wsUrl = window.TROFEU_WS_URL;
    if (wsUrl) {
        connectWs(wsUrl);
    } else {
        startPolling();
    }
})();
