const getCsrfToken = () => document.querySelector('meta[name="csrf-token"]')?.content ?? '';

const parseJsonResponse = async (response) => {
    const data = await response.json().catch(() => ({}));

    if (! response.ok) {
        const message = data.message
            ?? Object.values(data.errors ?? {}).flat()[0]
            ?? 'Request failed.';

        throw new Error(message);
    }

    return data;
};

const teamMembersRequest = async (url, method, body = undefined) => {
    const headers = {
        Accept: 'application/json',
        'X-CSRF-TOKEN': getCsrfToken(),
        'X-Requested-With': 'XMLHttpRequest',
    };

    if (body !== undefined) {
        headers['Content-Type'] = 'application/json';
    }

    const response = await fetch(url, {
        method,
        headers,
        body: body !== undefined ? JSON.stringify(body) : undefined,
    });

    return parseJsonResponse(response);
};

const setRowBusy = (row, isBusy) => {
    row.classList.toggle('is-busy', isBusy);
    row.querySelectorAll('select, button').forEach((element) => {
        element.disabled = isBusy;
    });
};

const showRowError = (row, message) => {
    const error = row?.querySelector('[data-team-member-error]');

    if (! error) {
        return;
    }

    if (message) {
        error.textContent = message;
        error.hidden = false;
    } else {
        error.textContent = '';
        error.hidden = true;
    }
};

const showPanelError = (panel, message) => {
    const error = panel.querySelector('[data-team-members-panel-error]');

    if (! error) {
        return;
    }

    if (message) {
        error.textContent = message;
        error.hidden = false;
    } else {
        error.textContent = '';
        error.hidden = true;
    }
};

const updateMemberCount = (panel, countLabel) => {
    const count = panel.querySelector('[data-team-members-count]');

    if (count && countLabel) {
        count.textContent = countLabel;
    }
};

const toggleEmptyState = (panel) => {
    const list = panel.querySelector('[data-team-members-list]');
    const empty = panel.querySelector('[data-team-members-empty]');
    const tableWrap = panel.querySelector('[data-team-members-table]');

    if (! list || ! empty) {
        return;
    }

    const hasMembers = list.querySelector('[data-team-member-row]') !== null;

    empty.hidden = hasMembers;
    if (tableWrap) {
        tableWrap.hidden = ! hasMembers;
    }
};

const initTeamMemberRoleSelects = (panel) => {
    panel.querySelectorAll('[data-team-member-role]').forEach((select) => {
        select.dataset.previousValue = select.value;

        select.addEventListener('change', async () => {
            const row = select.closest('[data-team-member-row]');
            const previousValue = select.dataset.previousValue ?? select.value;
            const url = select.dataset.url;

            if (! row || ! url || select.value === previousValue) {
                return;
            }

            showRowError(row, null);
            showPanelError(panel, null);
            setRowBusy(row, true);

            try {
                const data = await teamMembersRequest(url, 'PATCH', { role: select.value });
                select.dataset.previousValue = select.value;
                showRowError(row, null);

                if (data.member?.role_label) {
                    const selectedOption = select.querySelector(`option[value="${select.value}"]`);

                    if (selectedOption) {
                        selectedOption.textContent = data.member.role_label;
                    }
                }
            } catch (error) {
                select.value = previousValue;
                showRowError(row, error.message);
            } finally {
                setRowBusy(row, false);
            }
        });
    });
};

const initTeamMemberRemoveButtons = (panel) => {
    panel.querySelectorAll('[data-team-member-remove]').forEach((button) => {
        button.addEventListener('click', async (event) => {
            event.preventDefault();

            const row = button.closest('[data-team-member-row]');
            const url = button.dataset.url;

            if (! row || ! url) {
                return;
            }

            showRowError(row, null);
            showPanelError(panel, null);
            setRowBusy(row, true);

            try {
                const data = await teamMembersRequest(url, 'DELETE');

                row.remove();
                updateMemberCount(panel, data.member_count_label);
                toggleEmptyState(panel);
                showPanelError(panel, null);
            } catch (error) {
                showRowError(row, error.message);
            } finally {
                if (row.isConnected) {
                    setRowBusy(row, false);
                }
            }
        });
    });
};

export const initTeamMembers = () => {
    document.querySelectorAll('[data-team-members]').forEach((panel) => {
        initTeamMemberRoleSelects(panel);
        initTeamMemberRemoveButtons(panel);
        toggleEmptyState(panel);
    });
};
