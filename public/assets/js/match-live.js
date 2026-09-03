(function () {
    const page = document.getElementById('match-live-page');
    if (!page) return;

    const matchId = window.TROFEU_MATCH_ID;
    const apiUrl = window.TROFEU_MATCH_API;
    const team1Id = window.TROFEU_TEAM1;
    const timeline = document.getElementById('goals-timeline');
    const connBadge = document.getElementById('ws-conn-badge');

    const ICO_BALL = '<svg class="icon icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 3a12 12 0 0 1 0 18M12 3a12 12 0 0 0 0 18M3 12h18M5.5 6.5l13 11M5.5 17.5l13-11"/></svg>';
    const ICO_STAR = '<svg class="icon icon-sm motm-star-svg" viewBox="0 0 24 24" fill="currentColor" stroke="none" aria-hidden="true"><path d="M12 3.5 14.5 9l6 .5-4.6 4 1.4 5.8L12 16.5 6.7 19.3 8.1 13.5 3.5 9.5 9.5 9z"/></svg>';

    function escapeHtml(s) {
        const d = document.createElement('div');
        d.textContent = s;
        return d.innerHTML;
    }

    function renderGoals(goals) {
        if (!timeline) return;
        if (!goals || !goals.length) {
            timeline.innerHTML = '<p class="text-muted" id="no-goals-msg">Niciun gol înregistrat încă.</p>';
            return;
        }
        timeline.innerHTML = goals.map(g => {
            const isHome = g.team_id === team1Id;
            const name = g.player_name || ((g.prenume || '') + ' ' + (g.nume || '')).trim() || 'Jucător necunoscut';
            const team = g.team_nume || '';
            return `<div class="goal-event ${isHome ? 'team-home' : 'team-away'}" data-goal-id="${g.id}">
                <span class="goal-minute">${g.minute ? g.minute + "'" : ICO_BALL}</span>
                <div class="goal-body"><strong>${escapeHtml(name)}</strong><span class="text-muted">${escapeHtml(team)}</span></div>
            </div>`;
        }).join('');
    }

    function renderMotm(motm1, motm2) {
        const box = document.getElementById('motm-display');
        if (!box) return;
        const parts = [];
        if (motm1) {
            parts.push(`<div class="motm-card"><span class="motm-star">${ICO_STAR}</span><img src="${escapeHtml(motm1.photo || motm1.poza_path || '')}" alt=""><strong>${escapeHtml(motm1.name)}</strong><small>Echipa gazdă</small></div>`);
        }
        if (motm2) {
            parts.push(`<div class="motm-card"><span class="motm-star">${ICO_STAR}</span><img src="${escapeHtml(motm2.photo || motm2.poza_path || '')}" alt=""><strong>${escapeHtml(motm2.name)}</strong><small>Echipa oaspete</small></div>`);
        }
        box.innerHTML = parts.length ? parts.join('') : '<p class="text-muted">Oamenii meciului vor fi anunțați după terminare.</p>';
        box.style.display = parts.length ? '' : '';
    }

    function flashScore() {
        const el = document.getElementById('live-score-text');
        if (!el) return;
        el.classList.remove('score-flash');
        void el.offsetWidth;
        el.classList.add('score-flash');
    }

    function updateMatch(payload) {
        const m = payload.match || payload;
        const prevScore = document.getElementById('live-score-text')?.textContent;
        const scoreEl = document.getElementById('live-score-text');
        const badge = document.getElementById('live-status-badge');
        const board = document.getElementById('live-scoreboard');
        const newScore = (m.scor_echipa1 ?? 0) + ' : ' + (m.scor_echipa2 ?? 0);

        if (scoreEl) {
            scoreEl.textContent = newScore;
            if (prevScore && prevScore !== newScore) flashScore();
        }
        if (badge) {
            badge.className = 'status-badge status-' + m.status;
            badge.innerHTML = m.status === 'se_joaca'
                ? '<span class="live-dot"></span> LIVE'
                : (m.status === 'terminat' ? 'Terminat' : 'Programat');
        }
        if (board) {
            board.classList.toggle('is-live', m.status === 'se_joaca');
            board.classList.toggle('is-finished', m.status === 'terminat');
        }
        if (payload.goals) renderGoals(payload.goals);
        if (payload.motm1 !== undefined || payload.motm2 !== undefined) {
            renderMotm(payload.motm1, payload.motm2);
        } else if (m.motm1 || m.motm2) {
            renderMotm(m.motm1, m.motm2);
        }
    }

    function normalizePayload(msg) {
        const data = msg.data || msg;
        if (!data || (data.id !== matchId && data.match?.id !== matchId)) return null;
        const goals = data.goals || msg.goals;
        const match = data.match || data;
        return {
            match,
            goals,
            motm1: data.motm1 || match.motm1,
            motm2: data.motm2 || match.motm2,
        };
    }

    let wsConnected = false;
    let pollTimer = null;

    function startPolling() {
        if (pollTimer || !apiUrl) return;
        pollTimer = setInterval(async () => {
            if (wsConnected) return;
            try {
                const res = await fetch(apiUrl, { cache: 'no-store' });
                const json = await res.json();
                if (json.success) updateMatch(json.data);
            } catch (e) {}
        }, 30000);
    }

    function stopPolling() {
        if (pollTimer) { clearInterval(pollTimer); pollTimer = null; }
    }

    function setConnBadge(live) {
        if (!connBadge) return;
        connBadge.className = 'conn-badge ' + (live ? 'conn-live' : 'conn-poll');
        connBadge.textContent = live ? '● Conectat live' : '○ Reconectare…';
    }

    const wsUrl = window.TROFEU_WS_URL;
    if (wsUrl) {
        let ws;
        function connect() {
            try { ws = new WebSocket(wsUrl); } catch (e) { startPolling(); return; }
            ws.onopen = () => {
                wsConnected = true;
                stopPolling();
                setConnBadge(true);
                ws.send(JSON.stringify({ type: 'ping' }));
            };
            ws.onmessage = (ev) => {
                try {
                    const msg = JSON.parse(ev.data);
                    if (msg.type === 'pong') return;
                    const payload = normalizePayload(msg);
                    if (payload) updateMatch(payload);
                } catch (e) {}
            };
            ws.onclose = () => {
                wsConnected = false;
                setConnBadge(false);
                startPolling();
                setTimeout(connect, 1500);
            };
            ws.onerror = () => {
                wsConnected = false;
                setConnBadge(false);
                startPolling();
            };
        }
        connect();
    } else {
        setConnBadge(false);
        startPolling();
    }

    if (apiUrl) {
        fetch(apiUrl, { cache: 'no-store' })
            .then(r => r.json())
            .then(json => { if (json.success) updateMatch(json.data); })
            .catch(() => {});
    }
})();
