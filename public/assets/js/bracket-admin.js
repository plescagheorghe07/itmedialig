(function () {
    const form = document.getElementById('bracket-admin-form');
    const editor = document.getElementById('bracket-editor');
    if (!form || !editor) return;

    function getWinner(team1, team2, s1, s2) {
        if (s1 === '' || s2 === '' || s1 === s2) return null;
        return parseInt(s1, 10) > parseInt(s2, 10) ? team1 : team2;
    }

    function teamName(select) {
        if (!select || !select.value) return 'TBD';
        return select.options[select.selectedIndex].text;
    }

    function advanceWinners() {
        const cards = [...editor.querySelectorAll('.bracket-match-card')];
        cards.sort((a, b) => {
            const ra = parseInt(a.dataset.round, 10);
            const rb = parseInt(b.dataset.round, 10);
            if (ra !== rb) return ra - rb;
            return parseInt(a.dataset.match, 10) - parseInt(b.dataset.match, 10);
        });

        cards.forEach(card => {
            const r = parseInt(card.dataset.round, 10);
            const m = parseInt(card.dataset.match, 10);
            const t1 = card.querySelector('[data-slot="team1"]');
            const t2 = card.querySelector('[data-slot="team2"]');
            const s1 = card.querySelector('[name*="[score1]"]');
            const s2 = card.querySelector('[name*="[score2]"]');
            const winner = getWinner(t1.value, t2.value, s1.value, s2.value);

            let hint = card.querySelector('.bracket-winner-hint');
            if (winner) {
                if (!hint) {
                    hint = document.createElement('div');
                    hint.className = 'bracket-winner-hint';
                    card.appendChild(hint);
                }
                hint.textContent = '→ ' + (winner === t1.value ? teamName(t1) : teamName(t2));
            } else if (hint) {
                hint.remove();
            }

            if (!winner) return;
            const nextRound = r + 1;
            const nextMatch = Math.floor(m / 2);
            const slot = m % 2 === 0 ? 'team1' : 'team2';
            const nextCard = editor.querySelector(`.bracket-match-card[data-round="${nextRound}"][data-match="${nextMatch}"]`);
            if (!nextCard) return;
            const nextSelect = nextCard.querySelector(`[data-slot="${slot}"]`);
            if (nextSelect && !nextSelect.dataset.manual) {
                nextSelect.value = winner;
            }
        });
    }

    editor.querySelectorAll('.bracket-team-select').forEach(sel => {
        sel.addEventListener('change', () => { sel.dataset.manual = '1'; advanceWinners(); });
    });
    editor.querySelectorAll('.bracket-score-input').forEach(inp => {
        inp.addEventListener('input', advanceWinners);
    });

    const sizeSelect = document.getElementById('bracket-size-select');
    const sizeForm = document.querySelector('.bracket-size-form');
    if (sizeSelect && sizeForm) {
        const currentSize = sizeSelect.dataset.current || sizeSelect.value;
        sizeSelect.addEventListener('change', function () {
            const newSize = this.value;
            if (!confirm('Schimbi la ' + newSize + ' echipe? Structura actuală va fi resetată.')) {
                this.value = currentSize;
                return;
            }
            window.location.href = sizeForm.action + '?size=' + encodeURIComponent(newSize) + '&init=1';
        });
    }

    advanceWinners();
})();
