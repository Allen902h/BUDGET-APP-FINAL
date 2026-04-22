let deferredInstallPrompt = null;
let loadingOverlayController = null;

document.addEventListener('DOMContentLoaded', () => {
    loadingOverlayController = initializeLoadingOverlay();
    initializeMotionSystem();
    fadeFlashMessages();
    renderDashboardCharts();
    prepareCsvPreview();
    enableInstallPrompt();
    fireBrowserNotifications();
    enableOfflineExpenseQueue();
    bindTransactionTypeFields();
    bindSavingsGoalEditors();
    bindDestructivePrompts();
});

window.addEventListener('beforeinstallprompt', (event) => {
    event.preventDefault();
    deferredInstallPrompt = event;
    document.querySelectorAll('.install-trigger').forEach((button) => {
        button.hidden = false;
    });
});

window.addEventListener('online', () => {
    syncQueuedTransactions();
});

window.addEventListener('load', () => {
    loadingOverlayController?.hide();
});

function fadeFlashMessages() {
    document.querySelectorAll('.flash-success').forEach((alert) => {
        setTimeout(() => {
            alert.classList.add('is-dismissing');
            setTimeout(() => alert.remove(), 450);
        }, 3000);
    });
}

function initializeMotionSystem() {
    document.body.classList.add('js-enhanced');

    window.requestAnimationFrame(() => {
        document.body.classList.add('motion-ready');
    });

    initializeRouteTransitions();
    initializeRevealObserver();
}

function initializeLoadingOverlay() {
    const overlay = document.querySelector('[data-loading-overlay]');
    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    if (!overlay) {
        return null;
    }

    const show = (message) => {
        const text = overlay.querySelector('.app-loading-text');

        if (text && message) {
            text.textContent = message;
        }

        overlay.classList.add('is-active');
        overlay.setAttribute('aria-hidden', 'false');
        document.body.classList.add('is-loading');
    };

    const hide = () => {
        const completeHide = () => {
            overlay.classList.remove('is-active');
            overlay.setAttribute('aria-hidden', 'true');
            document.body.classList.remove('is-loading');
        };

        if (prefersReducedMotion) {
            completeHide();
            return;
        }

        window.setTimeout(completeHide, 120);
    };

    document.addEventListener('submit', (event) => {
        const form = event.target;

        if (!(form instanceof HTMLFormElement)) {
            return;
        }

        window.setTimeout(() => {
            if (
                event.defaultPrevented ||
                form.target === '_blank' ||
                form.hasAttribute('data-skip-loading')
            ) {
                return;
            }

            show('Saving your changes and loading the next screen.');
        }, 0);
    });

    return { show, hide };
}

function initializeRouteTransitions() {
    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    if (prefersReducedMotion) {
        return;
    }

    document.addEventListener('click', (event) => {
        const link = event.target.closest('a[href]');

        if (!link) {
            return;
        }

        const href = link.getAttribute('href') || '';

        if (
            event.defaultPrevented ||
            event.button !== 0 ||
            event.metaKey ||
            event.ctrlKey ||
            event.shiftKey ||
            event.altKey ||
            link.target === '_blank' ||
            link.hasAttribute('download') ||
            href.startsWith('#') ||
            href.startsWith('mailto:') ||
            href.startsWith('tel:')
        ) {
            return;
        }

        const destination = new URL(link.href, window.location.href);

        if (
            destination.origin !== window.location.origin ||
            `${destination.pathname}${destination.search}${destination.hash}` === `${window.location.pathname}${window.location.search}${window.location.hash}`
        ) {
            return;
        }

        event.preventDefault();
        loadingOverlayController?.show('Loading the next page.');
        document.body.classList.add('is-routing');
        window.setTimeout(() => {
            window.location.href = destination.toString();
        }, 180);
    });
}

function initializeRevealObserver() {
    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const revealGroups = [
        { selector: '.hero-panel', motion: 'left' },
        { selector: '.hero-copy, .showcase-copy, .auth-card', motion: 'left' },
        { selector: '.hero-card-stack, .hero-inline-card, .showcase-visual, .media-frame', motion: 'right' },
        { selector: '.feature-card, .workspace-card, .detail-card, .dashboard-kpi, .board-panel, .sidebar-panel, .sidebar-mini-card', motion: 'zoom' },
        { selector: '.dashboard-main-header, .dashboard-identity, .dashboard-frame-header, .topbar-inner', motion: 'left' },
        { selector: '.alert-row, .compact-row, .goal-card, .history-card, .setting-card', motion: 'zoom' },
    ];

    revealGroups.forEach(({ selector, motion }) => {
        document.querySelectorAll(selector).forEach((element, index) => {
            element.classList.add('motion-reveal');
            element.dataset.motion = element.dataset.motion || motion;
            element.style.setProperty('--motion-delay', `${Math.min(index, 5) * 60}ms`);
        });
    });

    document.querySelectorAll('.detail-grid, .hero-badges, .dashboard-kpi-grid, .board-stack, .sidebar-nav, .sidebar-mini-stats, .insights-summary, .insights-grid, .insights-stack, .insights-side-stack').forEach((group) => {
        group.classList.add('stagger-group');
        group.querySelectorAll(':scope > .motion-reveal').forEach((child, index) => {
            child.style.setProperty('--motion-delay', `${index * 70}ms`);
        });
    });

    const revealItems = [...document.querySelectorAll('.motion-reveal')];

    if (prefersReducedMotion || !('IntersectionObserver' in window)) {
        revealItems.forEach((item) => item.classList.add('is-visible'));
        return;
    }

    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (!entry.isIntersecting) {
                return;
            }

            entry.target.classList.add('is-visible');
            observer.unobserve(entry.target);
        });
    }, {
        threshold: 0.18,
        rootMargin: '0px 0px -8% 0px',
    });

    revealItems.forEach((item) => observer.observe(item));
}

function renderDashboardCharts() {
    if (typeof Chart === 'undefined') {
        return;
    }

    renderDoughnutChart();
    renderBudgetVsActualChart();
    renderTrendChart();
}

function renderDoughnutChart() {
    const chartCanvas = document.getElementById('categoryChart');
    if (!chartCanvas) {
        return;
    }

    const labels = JSON.parse(chartCanvas.dataset.labels || '[]');
    const values = JSON.parse(chartCanvas.dataset.values || '[]');
    if (!labels.length || !values.length) {
        return;
    }

    new Chart(chartCanvas, {
        type: 'doughnut',
        data: {
            labels,
            datasets: [{
                data: values,
                backgroundColor: ['#d97706', '#0f766e', '#1d4ed8', '#ea580c', '#7c3aed', '#0f172a', '#f59e0b'],
                borderColor: '#fffaf5',
                borderWidth: 3,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                },
            },
        },
    });
}

function renderBudgetVsActualChart() {
    const chartCanvas = document.getElementById('budgetActualChart');
    if (!chartCanvas) {
        return;
    }

    const labels = JSON.parse(chartCanvas.dataset.labels || '[]');
    const budget = JSON.parse(chartCanvas.dataset.budget || '[]');
    const spent = JSON.parse(chartCanvas.dataset.spent || '[]');
    if (!labels.length) {
        return;
    }

    new Chart(chartCanvas, {
        type: 'bar',
        data: {
            labels,
            datasets: [
                {
                    label: 'Budget',
                    data: budget,
                    backgroundColor: 'rgba(29, 78, 216, 0.72)',
                    borderRadius: 10,
                },
                {
                    label: 'Actual',
                    data: spent,
                    backgroundColor: 'rgba(217, 119, 6, 0.78)',
                    borderRadius: 10,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                },
            },
        },
    });
}

function renderTrendChart() {
    const chartCanvas = document.getElementById('trendChart');
    if (!chartCanvas) {
        return;
    }

    const labels = JSON.parse(chartCanvas.dataset.labels || '[]');
    const income = JSON.parse(chartCanvas.dataset.income || '[]');
    const expenses = JSON.parse(chartCanvas.dataset.expenses || '[]');
    if (!labels.length) {
        return;
    }

    new Chart(chartCanvas, {
        type: 'line',
        data: {
            labels,
            datasets: [
                {
                    label: 'Income',
                    data: income,
                    borderColor: '#0f766e',
                    backgroundColor: 'rgba(15, 118, 110, 0.16)',
                    tension: 0.3,
                    fill: true,
                },
                {
                    label: 'Expenses',
                    data: expenses,
                    borderColor: '#dc2626',
                    backgroundColor: 'rgba(220, 38, 38, 0.12)',
                    tension: 0.3,
                    fill: true,
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                },
            },
        },
    });
}

function prepareCsvPreview() {
    const input = document.getElementById('csv_file');
    const preview = document.querySelector('.csv-preview');
    const output = document.querySelector('.csv-preview-output');
    const dropzone = document.querySelector('.dropzone');

    if (!input || !preview || !output || !dropzone) {
        return;
    }

    ['dragenter', 'dragover'].forEach((eventName) => {
        dropzone.addEventListener(eventName, (event) => {
            event.preventDefault();
            dropzone.classList.add('is-dragging');
        });
    });

    ['dragleave', 'drop'].forEach((eventName) => {
        dropzone.addEventListener(eventName, () => {
            dropzone.classList.remove('is-dragging');
        });
    });

    input.addEventListener('change', () => {
        const file = input.files?.[0];
        if (!file) {
            return;
        }

        const reader = new FileReader();
        reader.onload = () => {
            const text = String(reader.result || '');
            const rows = text.split(/\r?\n/).slice(0, 6).join('\n');
            output.textContent = rows;
            preview.hidden = false;
        };
        reader.readAsText(file);
    });
}

function enableInstallPrompt() {
    document.querySelectorAll('.install-trigger').forEach((button) => {
        button.addEventListener('click', async () => {
            if (!deferredInstallPrompt) {
                return;
            }

            deferredInstallPrompt.prompt();
            await deferredInstallPrompt.userChoice;
            deferredInstallPrompt = null;
            button.hidden = true;
        });
    });
}

function fireBrowserNotifications() {
    if (!('Notification' in window)) {
        return;
    }

    const alerts = [...document.querySelectorAll('.notification-source')].slice(0, 3);
    if (!alerts.length) {
        return;
    }

    const showNotifications = () => {
        alerts.forEach((alert) => {
            const title = alert.dataset.notificationTitle || 'Budget Alert';
            const body = alert.dataset.notificationBody || alert.textContent.trim();
            new Notification(title, { body });
        });
    };

    if (Notification.permission === 'granted') {
        showNotifications();
        return;
    }

    if (Notification.permission === 'default') {
        Notification.requestPermission().then((permission) => {
            if (permission === 'granted') {
                showNotifications();
            }
        });
    }
}

function bindTransactionTypeFields() {
    document.querySelectorAll('form').forEach((form) => {
        const typeSelect = form.querySelector('.transaction-type-select');
        const categoryField = form.querySelector('.transaction-category-field');
        const categorySelect = categoryField?.querySelector('select[name="category_id"]');
        const typeHint = form.querySelector('[data-transaction-type-hint]');

        if (!typeSelect || !categoryField || !categorySelect) {
            return;
        }

        const syncVisibility = () => {
            const isExpense = typeSelect.value === 'expense';
            categoryField.style.display = isExpense ? '' : 'none';
            categorySelect.required = isExpense;

            if (typeHint) {
                typeHint.textContent = isExpense
                    ? 'Choose expense when money goes out. Category is required so reports can group the spending correctly.'
                    : 'Choose income when money comes in. Category becomes optional because this entry adds to available funds.';
            }

            if (!isExpense) {
                categorySelect.value = '';
            }
        };

        typeSelect.addEventListener('change', syncVisibility);
        syncVisibility();
    });
}

function enableOfflineExpenseQueue() {
    const form = document.querySelector('.offline-transaction-form');
    if (!form) {
        syncQueuedTransactions();
        return;
    }

    form.addEventListener('submit', async (event) => {
        if (navigator.onLine) {
            return;
        }

        event.preventDefault();

        const formData = new FormData(form);
        const payload = {
            cycle_id: Number(formData.get('cycle_id')),
            category_id: formData.get('category_id') ? Number(formData.get('category_id')) : null,
            transaction_type: String(formData.get('transaction_type') || 'expense'),
            amount: Number(formData.get('amount')),
            timestamp: formData.get('timestamp') || null,
            note: formData.get('note') || null,
        };

        const queue = readOfflineQueue();
        queue.push(payload);
        localStorage.setItem('budget.offlineTransactions', JSON.stringify(queue));
        form.reset();
        bindTransactionTypeFields();

        window.alert('You are offline. The entry was stored and will sync automatically when you reconnect.');
    });

    syncQueuedTransactions();
}

function bindSavingsGoalEditors() {
    document.querySelectorAll('[data-goal-editor]').forEach((form) => {
        const submitButton = form.querySelector('[data-goal-submit]');
        const warning = form.querySelector('[data-goal-warning]');
        const deadlineText = form.querySelector('[data-deadline-text]');
        const summaryRemaining = form.querySelector('[data-summary-remaining]');
        const summarySaved = form.querySelector('[data-summary-saved]');
        const summaryStatus = form.querySelector('[data-summary-status]');
        const summaryMonthly = form.querySelector('[data-summary-monthly]');
        const createdText = form.querySelector('[data-goal-created-text]');
        const updatedText = form.querySelector('[data-goal-updated-text]');
        const liveTimer = form.querySelector('[data-goal-live-timer]');
        const targetInput = form.querySelector('input[name="target_amount"]');
        const currentInput = form.querySelector('input[name="current_amount"]');
        const dateInput = form.querySelector('input[name="target_date"]');
        const currencySymbol = form.dataset.currencySymbol || '';
        const createdAt = parseGoalDateTime(form.dataset.goalCreatedAt);
        const updatedAt = parseGoalDateTime(form.dataset.goalUpdatedAt);

        if (!submitButton || !warning || !targetInput || !currentInput || !dateInput) {
            return;
        }

        const initialState = serializeGoalForm(form);

        const updateState = () => {
            const targetAmount = parseFloat(targetInput.value || '0');
            const currentAmount = parseFloat(currentInput.value || '0');
            const remaining = Math.max(targetAmount - currentAmount, 0);
            const progress = targetAmount > 0 ? Math.min((currentAmount / targetAmount) * 100, 100) : 0;
            const warningMessages = [];
            const changed = serializeGoalForm(form) !== initialState;
            const dateValue = dateInput.value;
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            const selectedDate = dateValue ? new Date(`${dateValue}T00:00:00`) : null;

            targetInput.setCustomValidity('');
            currentInput.setCustomValidity('');
            dateInput.setCustomValidity('');

            if (!targetInput.value || targetAmount <= 0) {
                targetInput.setCustomValidity('Target amount is required.');
                warningMessages.push('Target amount is required and must be greater than zero.');
            }

            if (currentAmount < 0) {
                currentInput.setCustomValidity('Current saved cannot be negative.');
                warningMessages.push('Current saved cannot be negative.');
            }

            if (targetAmount > 0 && currentAmount > targetAmount) {
                warningMessages.push('Current saved is already higher than target amount.');
            }

            if (selectedDate && selectedDate < today) {
                dateInput.setCustomValidity('Target date cannot be in the past.');
                warningMessages.push('Target date cannot be in the past.');
            }

            if (summaryRemaining) {
                summaryRemaining.textContent = `${currencySymbol}${formatCurrencyValue(remaining)}`;
            }

            if (summarySaved) {
                summarySaved.textContent = `${formatPercentageValue(progress)}%`;
            }

            if (summaryStatus) {
                summaryStatus.textContent = progress >= 100 ? 'Completed' : 'In progress';
            }

            if (summaryMonthly) {
                const monthly = remaining > 0 ? remaining / getMonthEstimate(selectedDate) : 0;
                summaryMonthly.textContent = `${currencySymbol}${formatCurrencyValue(monthly)}/month`;
            }

            if (deadlineText) {
                deadlineText.textContent = formatDeadlineLabel(selectedDate);
            }

            if (liveTimer) {
                liveTimer.textContent = formatTargetCountdown(selectedDate);
            }

            if (createdText && createdAt) {
                createdText.textContent = formatDateTimeLabel(createdAt);
            }

            if (updatedText) {
                updatedText.textContent = changed ? 'Pending new update after save' : formatDateTimeLabel(updatedAt || createdAt);
            }

            warning.hidden = warningMessages.length === 0;
            warning.textContent = warningMessages.join(' ');

            submitButton.disabled = warningMessages.some((message) => !message.includes('higher than target amount'));
            submitButton.textContent = changed ? 'Update Goal' : 'Save Goal';
        };

        form.addEventListener('input', updateState);
        form.addEventListener('change', updateState);
        form.addEventListener('submit', () => {
            submitButton.disabled = true;
            submitButton.textContent = 'Updating...';
        });
        updateState();
        window.setInterval(updateState, 60000);
    });
}

function bindDestructivePrompts() {
    document.querySelectorAll('form[data-delete-confirm]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            const label = form.dataset.deleteLabel || 'this item';
            const confirmation = window.prompt(`Type DELETE to remove ${label}. This helps prevent accidental deletion.`);

            if (confirmation !== 'DELETE') {
                event.preventDefault();
            }
        });
    });
}

function serializeGoalForm(form) {
    return JSON.stringify({
        name: form.querySelector('input[name="name"]')?.value || '',
        target_amount: form.querySelector('input[name="target_amount"]')?.value || '',
        current_amount: form.querySelector('input[name="current_amount"]')?.value || '',
        target_date: form.querySelector('input[name="target_date"]')?.value || '',
        notes: form.querySelector('textarea[name="notes"]')?.value || '',
    });
}

function formatCurrencyValue(value) {
    return Number(value || 0).toLocaleString(undefined, {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });
}

function formatPercentageValue(value) {
    return Number(value || 0).toLocaleString(undefined, {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });
}

function formatDeadlineLabel(selectedDate) {
    if (!selectedDate || Number.isNaN(selectedDate.getTime())) {
        return 'No deadline set';
    }

    const today = new Date();
    today.setHours(0, 0, 0, 0);
    const diffDays = Math.round((selectedDate - today) / 86400000);

    if (diffDays < 0) {
        return `Past due by ${Math.abs(diffDays)} day${Math.abs(diffDays) === 1 ? '' : 's'}`;
    }

    if (diffDays === 0) {
        return 'Due today';
    }

    return `${diffDays} day${diffDays === 1 ? '' : 's'} left`;
}

function getMonthEstimate(selectedDate) {
    if (!selectedDate || Number.isNaN(selectedDate.getTime())) {
        return 3;
    }

    const today = new Date();
    const diffMonths = (selectedDate.getFullYear() - today.getFullYear()) * 12 + (selectedDate.getMonth() - today.getMonth());
    return Math.max(diffMonths, 1);
}

function parseGoalDateTime(value) {
    if (!value) {
        return null;
    }

    const parsed = new Date(value);
    return Number.isNaN(parsed.getTime()) ? null : parsed;
}

function formatDateTimeLabel(date) {
    if (!date) {
        return 'Not available';
    }

    return date.toLocaleString(undefined, {
        month: 'short',
        day: '2-digit',
        year: 'numeric',
        hour: 'numeric',
        minute: '2-digit',
    });
}

function formatTargetCountdown(selectedDate) {
    if (!selectedDate || Number.isNaN(selectedDate.getTime())) {
        return 'No target date set';
    }

    const now = new Date();
    const diffMs = selectedDate.getTime() - now.getTime();

    if (diffMs <= 0) {
        return 'Target date reached';
    }

    const totalHours = Math.floor(diffMs / 3600000);
    const days = Math.floor(totalHours / 24);
    const hours = totalHours % 24;

    if (days > 0) {
        return `${days} day${days === 1 ? '' : 's'} ${hours} hr left`;
    }

    return `${hours} hr left`;
}

function readOfflineQueue() {
    try {
        return JSON.parse(localStorage.getItem('budget.offlineTransactions') || '[]');
    } catch (error) {
        return [];
    }
}

async function syncQueuedTransactions() {
    const queue = readOfflineQueue();
    const form = document.querySelector('.offline-transaction-form');
    const syncUrl = form?.dataset.syncUrl;
    const token = document.querySelector('meta[name="csrf-token"]')?.content;

    if (!queue.length || !syncUrl || !token || !navigator.onLine) {
        return;
    }

    try {
        const response = await fetch(syncUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ transactions: queue }),
        });

        if (!response.ok) {
            throw new Error('Unable to sync offline transactions.');
        }

        localStorage.removeItem('budget.offlineTransactions');
        window.location.reload();
    } catch (error) {
        console.error(error);
    }
}
