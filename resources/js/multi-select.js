const initializedSelects = new WeakSet();

const selectedOptionsFor = (select) => Array.from(select.options).filter((option) => option.selected);

const labelFor = (select) => {
    if (select.id) {
        const label = document.querySelector(`label[for="${select.id}"]`);

        if (label?.textContent?.trim()) {
            return label.textContent.trim();
        }
    }

    return select.dataset.placeholder ?? 'Select options';
};

const optionDataFor = (select) => Array.from(select.options).map((option) => ({
    label: option.textContent.trim(),
    value: option.value,
    option,
}));

const updateNativeSelect = (select, selectedValues) => {
    Array.from(select.options).forEach((option) => {
        option.selected = selectedValues.has(option.value);
    });

    select.dispatchEvent(new Event('change', { bubbles: true }));
};

const createElement = (tagName, className, attributes = {}) => {
    const element = document.createElement(tagName);
    element.className = className;

    Object.entries(attributes).forEach(([attribute, value]) => {
        element.setAttribute(attribute, value);
    });

    return element;
};

const renderSelectedValues = ({ select, selectedValues, values, searchInput }) => {
    values.replaceChildren();

    selectedOptionsFor(select).forEach((option) => {
        const chip = createElement('span', 'multi-select__chip');
        chip.textContent = option.textContent.trim();

        const removeButton = createElement('button', 'multi-select__chip-remove', {
            type: 'button',
            'aria-label': `Remove ${option.textContent.trim()}`,
        });

        const removeIcon = createElement('i', 'fa-solid fa-xmark', {
            'aria-hidden': 'true',
        });

        removeButton.append(removeIcon);
        removeButton.addEventListener('click', (event) => {
            event.stopPropagation();
            selectedValues.delete(option.value);
            updateNativeSelect(select, selectedValues);
            renderSelectedValues({ select, selectedValues, values, searchInput });
        });

        chip.append(removeButton);
        values.append(chip);
    });

    searchInput.placeholder = selectedValues.size === 0
        ? select.dataset.placeholder ?? 'Select options'
        : '';
};

const renderOptions = ({ data, list, searchInput, selectedValues, emptyLabel, toggleValue }) => {
    const query = searchInput.value.trim().toLowerCase();
    const filteredData = data.filter((item) => item.label.toLowerCase().includes(query));

    list.replaceChildren();

    if (filteredData.length === 0) {
        const empty = createElement('div', 'multi-select__empty');
        empty.textContent = emptyLabel;
        list.append(empty);

        return;
    }

    filteredData.forEach((item) => {
        const optionButton = createElement('button', 'multi-select__option', {
            type: 'button',
            role: 'option',
            'aria-selected': String(selectedValues.has(item.value)),
        });
        optionButton.dataset.value = item.value;

        const label = createElement('span', '');
        label.textContent = item.label;

        const checkIcon = createElement('i', 'fa-solid fa-check', {
            'aria-hidden': 'true',
        });

        optionButton.append(label, checkIcon);
        optionButton.classList.toggle('is-selected', selectedValues.has(item.value));
        optionButton.addEventListener('click', () => toggleValue(item.value));
        list.append(optionButton);
    });
};

const initMultiSelect = (select) => {
    if (initializedSelects.has(select)) {
        return;
    }

    initializedSelects.add(select);

    const selectedValues = new Set(selectedOptionsFor(select).map((option) => option.value));
    const data = optionDataFor(select);
    const labelledBy = select.id ? `${select.id}-multi-select-label` : null;
    const emptyLabel = select.dataset.emptyLabel ?? 'No matches';

    select.classList.add('task-form__native-multi-select');
    select.setAttribute('aria-hidden', 'true');
    select.tabIndex = -1;

    const root = createElement('div', 'multi-select', {
        'data-multi-select-ui': '',
    });
    const control = createElement('div', 'multi-select__control', {
        role: 'combobox',
        'aria-expanded': 'false',
        'aria-haspopup': 'listbox',
        'aria-label': labelFor(select),
    });
    const values = createElement('div', 'multi-select__values');
    const searchInput = createElement('input', 'multi-select__search', {
        type: 'text',
        autocomplete: 'off',
        'aria-label': select.dataset.searchLabel ?? `Search ${labelFor(select)}`,
    });
    const list = createElement('div', 'multi-select__list', {
        role: 'listbox',
        'aria-multiselectable': 'true',
    });

    if (labelledBy) {
        control.setAttribute('aria-labelledby', labelledBy);
    }

    control.append(values, searchInput);
    root.append(control, list);
    select.after(root);

    const close = () => {
        root.classList.remove('is-open');
        control.setAttribute('aria-expanded', 'false');
    };

    const open = () => {
        root.classList.add('is-open');
        control.setAttribute('aria-expanded', 'true');
        renderOptions({ data, list, searchInput, selectedValues, emptyLabel, toggleValue });
    };

    const toggleValue = (value) => {
        if (selectedValues.has(value)) {
            selectedValues.delete(value);
        } else {
            selectedValues.add(value);
        }

        updateNativeSelect(select, selectedValues);
        renderSelectedValues({ select, selectedValues, values, searchInput });
        renderOptions({ data, list, searchInput, selectedValues, emptyLabel, toggleValue });
        searchInput.focus();
    };

    renderSelectedValues({ select, selectedValues, values, searchInput });
    renderOptions({ data, list, searchInput, selectedValues, emptyLabel, toggleValue });

    control.addEventListener('click', () => {
        searchInput.focus();
        open();
    });

    searchInput.addEventListener('focus', open);
    searchInput.addEventListener('input', () => {
        open();
        renderOptions({ data, list, searchInput, selectedValues, emptyLabel, toggleValue });
    });

    searchInput.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            close();
            searchInput.blur();
        }

        if (event.key === 'Backspace' && searchInput.value === '' && selectedValues.size > 0) {
            const lastValue = Array.from(selectedValues).at(-1);
            selectedValues.delete(lastValue);
            updateNativeSelect(select, selectedValues);
            renderSelectedValues({ select, selectedValues, values, searchInput });
            renderOptions({ data, list, searchInput, selectedValues, emptyLabel, toggleValue });
        }
    });

    document.addEventListener('click', (event) => {
        if (! root.contains(event.target)) {
            close();
        }
    });
};

export const initMultiSelects = () => {
    document.querySelectorAll('select[data-multi-select][multiple]').forEach(initMultiSelect);
};
