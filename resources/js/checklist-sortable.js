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

const checklistRequest = async (url, taskIds) => {
    const response = await fetch(url, {
        method: 'PATCH',
        headers: {
            Accept: 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': getCsrfToken(),
            'X-Requested-With': 'XMLHttpRequest',
        },
        body: JSON.stringify({ task_ids: taskIds }),
    });

    return parseJsonResponse(response);
};

const taskIdsForList = (list) => Array.from(list.querySelectorAll('[data-checklist-sort-row]'))
    .map((row) => Number(row.dataset.taskId));

const setPanelBusy = (panel, isBusy) => {
    panel.classList.toggle('is-sorting', isBusy);

    panel.querySelectorAll('[data-checklist-sort-handle]').forEach((handle) => {
        handle.disabled = isBusy;
    });
};

const showSortError = (panel, message) => {
    const error = panel.querySelector('[data-checklist-sort-error]');

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

const setSortStatus = (panel, message) => {
    const status = panel.querySelector('[data-checklist-sort-status]');

    if (status && message) {
        status.textContent = message;
    }
};

const restoreOrder = (list, previousOrder) => {
    previousOrder.forEach((taskId) => {
        const row = list.querySelector(`[data-task-id="${taskId}"]`);

        if (row) {
            list.append(row);
        }
    });
};

const saveOrder = async (panel, list, previousOrder) => {
    const url = panel.dataset.url;

    if (! url) {
        return;
    }

    showSortError(panel, null);
    setPanelBusy(panel, true);

    try {
        await checklistRequest(url, taskIdsForList(list));
        setSortStatus(panel, 'Order saved');
    } catch (error) {
        restoreOrder(list, previousOrder);
        showSortError(panel, error.message);
    } finally {
        setPanelBusy(panel, false);
    }
};

const buildPlaceholder = (row) => {
    const placeholder = document.createElement('tr');
    const cell = document.createElement('td');

    placeholder.className = 'checklist-sort-placeholder';
    placeholder.dataset.checklistSortPlaceholder = 'true';
    placeholder.style.height = `${row.getBoundingClientRect().height}px`;

    cell.colSpan = row.children.length;
    placeholder.append(cell);

    return placeholder;
};

const buildGhost = (row, table, rect) => {
    const ghostTable = document.createElement('table');
    const ghostBody = document.createElement('tbody');
    const ghostRow = row.cloneNode(true);

    ghostTable.className = `${table.className} checklist-sort-ghost`;
    ghostTable.style.width = `${rect.width}px`;
    ghostTable.style.left = `${rect.left}px`;
    ghostTable.style.top = `${rect.top}px`;

    Array.from(row.children).forEach((cell, index) => {
        const ghostCell = ghostRow.children[index];

        if (ghostCell) {
            ghostCell.style.width = `${cell.getBoundingClientRect().width}px`;
        }
    });

    ghostRow.classList.add('is-dragging');
    ghostBody.append(ghostRow);
    ghostTable.append(ghostBody);
    document.body.append(ghostTable);

    return ghostTable;
};

const setGhostPosition = (ghost, pointerX, pointerY, offsetX, offsetY) => {
    ghost.style.left = `${pointerX - offsetX}px`;
    ghost.style.top = `${pointerY - offsetY}px`;
};

const maybeScrollViewport = (pointerY) => {
    const edgeSize = 72;
    const scrollStep = 14;

    if (pointerY < edgeSize) {
        window.scrollBy({ top: -scrollStep });
    } else if (window.innerHeight - pointerY < edgeSize) {
        window.scrollBy({ top: scrollStep });
    }
};

const getDropTarget = (list, pointerY) => {
    const rows = Array.from(list.querySelectorAll('[data-checklist-sort-row]'));

    return rows.reduce((closest, row) => {
        const box = row.getBoundingClientRect();
        const offset = pointerY - box.top - box.height / 2;

        if (offset < 0 && offset > closest.offset) {
            return { offset, row };
        }

        return closest;
    }, { offset: Number.NEGATIVE_INFINITY, row: null }).row;
};

const initChecklistSortablePanel = (panel) => {
    const list = panel.querySelector('[data-checklist-sort-list]');
    const table = panel.querySelector('table');

    if (! list || ! table) {
        return;
    }

    let activeRow = null;
    let activeHandle = null;
    let placeholder = null;
    let ghost = null;
    let previousOrder = [];
    let pointerOffsetX = 0;
    let pointerOffsetY = 0;
    let hasMoved = false;

    const cleanupSorting = () => {
        activeHandle?.classList.remove('is-active');
        ghost?.remove();

        ghost = null;
        activeHandle = null;
        placeholder = null;

        document.body.classList.remove('is-checklist-sorting');
        document.removeEventListener('pointermove', moveSortingRow);
        document.removeEventListener('pointerup', stopSorting);
        document.removeEventListener('pointercancel', cancelSorting);
    };

    const stopSorting = async () => {
        if (! activeRow || ! placeholder) {
            return;
        }

        const row = activeRow;
        const orderBeforeDrag = previousOrder;

        placeholder.replaceWith(row);
        activeRow = null;
        previousOrder = [];

        const orderChanged = taskIdsForList(list).join(',') !== orderBeforeDrag.join(',');

        cleanupSorting();

        if (hasMoved && orderChanged) {
            await saveOrder(panel, list, orderBeforeDrag);
        }

        hasMoved = false;
    };

    const cancelSorting = () => {
        if (! activeRow || ! placeholder) {
            return;
        }

        placeholder.replaceWith(activeRow);
        restoreOrder(list, previousOrder);

        activeRow = null;
        previousOrder = [];
        hasMoved = false;

        cleanupSorting();
    };

    function moveSortingRow(event) {
        if (! activeRow || ! placeholder || ! ghost) {
            return;
        }

        event.preventDefault();
        hasMoved = true;

        setGhostPosition(ghost, event.clientX, event.clientY, pointerOffsetX, pointerOffsetY);
        maybeScrollViewport(event.clientY);

        const target = getDropTarget(list, event.clientY);

        if (target) {
            list.insertBefore(placeholder, target);
        } else {
            list.append(placeholder);
        }
    }

    list.querySelectorAll('[data-checklist-sort-handle]').forEach((handle) => {
        handle.addEventListener('pointerdown', (event) => {
            if (event.button !== 0 || handle.disabled) {
                return;
            }

            const row = handle.closest('[data-checklist-sort-row]');

            if (! row) {
                return;
            }

            const rect = row.getBoundingClientRect();

            event.preventDefault();

            activeRow = row;
            activeHandle = handle;
            previousOrder = taskIdsForList(list);
            pointerOffsetX = event.clientX - rect.left;
            pointerOffsetY = event.clientY - rect.top;
            placeholder = buildPlaceholder(row);
            ghost = buildGhost(row, table, rect);
            hasMoved = false;

            handle.classList.add('is-active');
            row.replaceWith(placeholder);
            document.body.classList.add('is-checklist-sorting');

            document.addEventListener('pointermove', moveSortingRow);
            document.addEventListener('pointerup', stopSorting);
            document.addEventListener('pointercancel', cancelSorting);
        });
    });
};

export const initChecklistSortables = () => {
    document.querySelectorAll('[data-checklist-sortable]').forEach(initChecklistSortablePanel);
};
