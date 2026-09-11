(function () {
    const panel = document.getElementById('admin-match-panel');
    if (!panel) return;

    const api = panel.dataset.api;
    const csrf = panel.dataset.csrf;
    const motmSection = document.getElementById('motm-panel-section');
    const overlay = document.getElementById('eventOverlay');
    const overlayForm = document.getElementById('eventOverlayForm');
    let matchStatus = panel.dataset.status;
    let pendingPlayer = null;

    const ICONS = {
        goal: '<svg class="icon icon-sm" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 3a12 12 0 0 1 0 18M12 3a12 12 0 0 0 0 18M3 12h18M5.5 6.5l13 11M5.5 17.5l13-11"/></svg>',
        yellow_card: '<svg class="icon icon-sm" viewBox="0 0 24 24" aria-hidden="true"><rect x="7" y="3" width="10" height="18" rx="1.5" fill="#facc15" stroke="#ca8a04"/></svg>',
        red_card: '<svg class="icon icon-sm" viewBox="0 0 24 24" aria-hidden="true"><rect x="7" y="3" width="10" height="18" rx="1.5" fill="#ef4444" stroke="#b91c1c"/></svg>',
    };
    const TYPE_LABEL = { goal: 'Gol', yellow_card: 'Cartonaș galben', red_card: 'Cartonaș roșu' };

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

    function eventIcon(type) {
        return ICONS[type] || ICONS.goal;
    }

    function updateUI(data) {
        const m = data.match || data;
        const scoreEl = document.getElementById('panel-score');
        if (scoreEl && m.scor_echipa1 != null) {
            scoreEl.textContent = m.scor_echipa1 + ' : ' + m.scor_echipa2;
        }
        if (m.status) setStatus(m.status);

        const list = document.getElementById('panel-goals-list');
        const events = data.events || data.goals;
        if (list && events) {
            const canRemove = matchStatus === 'se_joaca';
            list.innerHTML = events.map(g => {
                const type = g.event_type || 'goal';
                const name = ((g.prenume || '') + ' ' + (g.nume || '')).trim() || g.player_name || '';
                return `<div class="panel-goal-item event-${type}"><span class="panel-goal-text">${eventIcon(type)} ${g.minute ? g.minute + "'" : ''} ${name} <small class="text-muted">${TYPE_LABEL[type] || type}</small></span> ${canRemove ? `<button type="button" class="btn btn-sm btn-ghost" data-remove-goal="${g.id}">×</button>` : ''}</div>`;
            }).join('');
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
                const r = await post('remove_event', { goal_id: btn.dataset.removeGoal });
                if (r.success) updateUI(r.data);
            };
        });
    }

    function openEventOverlay(btn) {
        pendingPlayer = {
            teamId: btn.dataset.teamId,
            playerId: btn.dataset.playerId,
            playerName: btn.dataset.playerName || 'Jucător',
        };
        document.getElementById('event-player-name').textContent = pendingPlayer.playerName;
        const minuteInput = document.getElementById('event-minute');
        minuteInput.value = '';
        const goalRadio = overlayForm.querySelector('input[name="event_type"][value="goal"]');
        if (goalRadio) goalRadio.checked = true;
        overlay.showModal();
        setTimeout(() => minuteInput.focus(), 50);
    }

    function closeEventOverlay() {
        pendingPlayer = null;
        if (overlay.open) overlay.close();
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
        btn.onclick = () => {
            if (matchStatus !== 'se_joaca') return;
            openEventOverlay(btn);
        };
    });

    overlayForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        if (!pendingPlayer || matchStatus !== 'se_joaca') return;
        const type = overlayForm.querySelector('input[name="event_type"]:checked')?.value || 'goal';
        const minute = document.getElementById('event-minute').value;
        if (!minute) {
            document.getElementById('event-minute').focus();
            return;
        }
        const confirmBtn = document.getElementById('event-confirm-btn');
        confirmBtn.disabled = true;
        try {
            const r = await post('event', {
                team_id: pendingPlayer.teamId,
                player_id: pendingPlayer.playerId,
                minute,
                event_type: type,
            });
            if (r.success) {
                updateUI(r.data);
                closeEventOverlay();
            } else {
                alert(r.error || 'Eroare');
            }
        } finally {
            confirmBtn.disabled = false;
        }
    });

    overlay.querySelectorAll('[data-event-cancel]').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            closeEventOverlay();
        });
    });
    overlay.addEventListener('click', (e) => {
        if (e.target === overlay) closeEventOverlay();
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
