(function () {
    const panel = document.getElementById('admin-match-panel');
    if (!panel) return;

    const api = panel.dataset.api;
    const csrf = panel.dataset.csrf;
    const motmSection = document.getElementById('motm-panel-section');
    let matchStatus = panel.dataset.status;

    const ICO_BALL = '<svg class="icon icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 3a12 12 0 0 1 0 18M12 3a12 12 0 0 0 0 18M3 12h18M5.5 6.5l13 11M5.5 17.5l13-11"/></svg>';

    async function post(action, extra = {}) {
        const fd = new FormData();
        fd.append('_csrf', csrf);
        fd.append('action', action);
        Object.entries(extra).forEach(([k, v]) => { if (v != null) fd.append(k, v); });
        const res = await fetch(api, { method: 'POST', body: fd });
        return res.json();
    }

    function setStatus(status) {
        matchStatus = status;
        panel.dataset.status = status;
        const badge = document.getElementById('panel-status');
        if (badge) {
            badge.className = 'status-badge status-' + status;
            badge.textContent = status;
        }
    }

    function updateUI(data) {
        const m = data.match || data;
        const scoreEl = document.getElementById('panel-score');
        if (scoreEl && m.scor_echipa1 != null) {
            scoreEl.textContent = m.scor_echipa1 + ' : ' + m.scor_echipa2;
        }
        if (m.status) setStatus(m.status);

        const list = document.getElementById('panel-goals-list');
        if (list && data.goals) {
            const canRemove = matchStatus === 'se_joaca';
            list.innerHTML = data.goals.map(g =>
                `<div class="panel-goal-item"><span class="panel-goal-text">${ICO_BALL} ${g.minute ? g.minute + "'" : ''} ${(g.prenume||'')+' '+(g.nume||'')}</span> ${canRemove ? `<button type="button" class="btn btn-sm btn-ghost" data-remove-goal="${g.id}">×</button>` : ''}</div>`
            ).join('');
            bindRemoveGoal();
        }
    }

    function showMotmSection() {
        if (!motmSection) return;
        motmSection.classList.remove('is-hidden');
        motmSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
        panel.querySelectorAll('.panel-player').forEach(b => { b.disabled = true; });
        document.querySelectorAll('[data-action="finish"]').forEach(b => b.remove());
        const toolbar = panel.querySelector('.panel-toolbar');
        if (toolbar) toolbar.innerHTML = '<span class="badge badge-finished">Meci terminat — alege oamenii meciului</span>';
    }

    function bindRemoveGoal() {
        document.querySelectorAll('[data-remove-goal]').forEach(btn => {
            btn.onclick = async () => {
                const r = await post('remove_goal', { goal_id: btn.dataset.removeGoal });
                if (r.success) updateUI(r.data);
            };
        });
    }

    panel.querySelectorAll('[data-action="start"]').forEach(btn => {
        btn.onclick = async () => {
            const r = await post('start');
            if (r.success) location.reload();
        };
    });

    panel.querySelectorAll('[data-action="finish"]').forEach(btn => {
        btn.onclick = async () => {
            if (!confirm('Termini meciul? Vei putea apoi selecta oamenii meciului.')) return;
            const r = await post('finish');
            if (r.success) {
                updateUI(r.data);
                showMotmSection();
            }
        };
    });

    panel.querySelectorAll('.panel-player:not([disabled])').forEach(btn => {
        btn.onclick = async () => {
            if (matchStatus !== 'se_joaca') return;
            const minute = document.getElementById('goal-minute')?.value || '';
            const r = await post('goal', {
                team_id: btn.dataset.teamId,
                player_id: btn.dataset.playerId,
                minute: minute
            });
            if (r.success) updateUI(r.data);
            else alert(r.error || 'Eroare');
        };
    });

    document.querySelectorAll('.motm-pick').forEach(btn => {
        btn.onclick = async () => {
            const side = btn.dataset.side;
            const r = await post('motm', { side, player_id: btn.dataset.playerId });
            if (!r.success) {
                alert(r.error || 'Nu s-a putut seta OM');
                return;
            }
            const block = btn.closest('.motm-team-block');
            block.querySelectorAll('.motm-pick').forEach(b => {
                b.classList.remove('is-selected');
                b.querySelector('.motm-badge')?.remove();
            });
            btn.classList.add('is-selected');
            if (!btn.querySelector('.motm-badge')) {
                const badge = document.createElement('span');
                badge.className = 'motm-badge';
                badge.textContent = 'OM';
                btn.appendChild(badge);
            }
        };
    });

    bindRemoveGoal();
})();
