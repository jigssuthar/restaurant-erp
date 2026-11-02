import './bootstrap';
import './operations-board';
import 'bootstrap/dist/js/bootstrap.bundle.min.js';
import { getApp, getApps, initializeApp } from '@firebase/app';
import { getMessaging, getToken, isSupported, onMessage } from '@firebase/messaging';

const firebasePushControl = document.querySelector('[data-firebase-push]');

if (firebasePushControl) {
    const config = JSON.parse(firebasePushControl.dataset.config);
    const status = firebasePushControl.querySelector('[data-firebase-push-status]');
    const savedTokenKey = 'firebase_push_device_token';
    let messaging;
    let foregroundListenerAttached = false;
    let registrationInProgress = false;
    const configReady = ['apiKey', 'authDomain', 'projectId', 'messagingSenderId', 'appId', 'vapidKey']
        .every((key) => typeof config[key] === 'string' && config[key] !== '');

    function listenForForegroundMessages(currentMessaging) {
        if (foregroundListenerAttached) return;
        onMessage(currentMessaging, (payload) => {
            window.dispatchEvent(new Event('firebase-push-received'));
            if (!payload.notification || Notification.permission !== 'granted') return;
            const notification = new Notification(payload.notification.title || 'DineCentral', {
                body: payload.notification.body || '',
                icon: '/favicon.ico',
                data: { url: payload.data?.click_url },
            });
            notification.onclick = () => {
                window.focus();
                if (notification.data.url) window.location.assign(notification.data.url);
            };
        });
        foregroundListenerAttached = true;
    }

    async function registerForPush(permissionPromise = Promise.resolve(Notification.permission)) {
        if (registrationInProgress) return;
        registrationInProgress = true;
        try {
            if (!configReady) throw new Error('Firebase web push is not configured.');
            if (!window.isSecureContext) {
                throw new Error('Push notifications require HTTPS (localhost is supported for development).');
            }

            const permission = await permissionPromise;
            if (permission !== 'granted') {
                if (permission === 'denied') {
                    status.textContent = 'Notifications are blocked in browser settings.';
                }
                return;
            }

            if (!('serviceWorker' in navigator) || !await isSupported()) {
                throw new Error('Push notifications are not supported by this browser.');
            }

            const app = getApps().length ? getApp() : initializeApp(config);
            messaging = getMessaging(app);
            const serviceWorkerRegistration = await navigator.serviceWorker.register(firebasePushControl.dataset.workerUrl);
            const token = await getToken(messaging, {
                vapidKey: config.vapidKey,
                serviceWorkerRegistration,
            });
            if (!token) throw new Error('Firebase did not return a browser registration token.');

            await saveFirebaseDevice(token, 'POST');
            localStorage.setItem(savedTokenKey, token);
            listenForForegroundMessages(messaging);
        } catch (error) {
            status.textContent = error.message || 'Could not enable push notifications.';
        } finally {
            registrationInProgress = false;
        }
    }

    if (!('Notification' in window)) {
        status.textContent = 'Push notifications are not supported by this browser.';
    } else if (Notification.permission === 'granted') {
        void registerForPush();
    } else if (Notification.permission === 'default') {
        const requestPermissionOnInteraction = (event) => {
            if (event.target.closest('form[action$="/logout"]')) return;
            document.removeEventListener('click', requestPermissionOnInteraction);
            void registerForPush(Notification.requestPermission());
        };
        document.addEventListener('click', requestPermissionOnInteraction);
    } else {
        status.textContent = 'Notifications are blocked in browser settings.';
    }

    const savedToken = localStorage.getItem(savedTokenKey);
    if (savedToken) {
        document.querySelectorAll('form[action$="/logout"]').forEach((form) => {
            const tokenInput = document.createElement('input');
            tokenInput.type = 'hidden';
            tokenInput.name = 'firebase_device_token';
            tokenInput.value = savedToken;
            form.append(tokenInput);
        });
    }

    async function saveFirebaseDevice(token, method) {
        const response = await fetch(firebasePushControl.dataset.registerUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-CSRF-TOKEN': firebasePushControl.dataset.csrfToken,
            },
            body: JSON.stringify({ token }),
        });
        const result = await response.json();
        if (!response.ok) throw new Error(result.message || 'Could not save push notification settings.');
    }
}

document.querySelectorAll('[data-in-app-notifications]').forEach((menu) => {
    const list = menu.querySelector('[data-notifications-list]');
    const count = menu.querySelector('[data-notification-count]');
    const status = menu.querySelector('[data-notifications-status]');
    const readAllButton = menu.querySelector('[data-notifications-read-all]');
    let requestInProgress = false;

    async function loadNotifications() {
        if (requestInProgress || document.hidden) return;
        requestInProgress = true;
        try {
            const response = await fetch(menu.dataset.feedUrl, {
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });
            const result = await response.json();
            if (!response.ok) throw new Error(result.message || 'Notifications could not be loaded.');

            count.textContent = result.unread_count > 99 ? '99+' : String(result.unread_count);
            count.classList.toggle('d-none', result.unread_count === 0);
            readAllButton.hidden = result.unread_count === 0;
            list.replaceChildren();

            if (!result.notifications.length) {
                status.textContent = 'No notifications yet.';
                list.append(status);
                return;
            }

            result.notifications.forEach((notification) => {
                const link = document.createElement('a');
                link.className = `list-group-item list-group-item-action${notification.read ? '' : ' bg-light'}`;
                link.href = notification.url;
                link.dataset.notificationId = notification.id;

                const title = document.createElement('div');
                title.className = 'fw-semibold small';
                title.textContent = notification.title;
                const body = document.createElement('div');
                body.className = 'small text-body-secondary text-wrap';
                body.textContent = notification.body;
                const time = document.createElement('div');
                time.className = 'small text-body-tertiary mt-1';
                time.textContent = notification.created_at || '';

                link.append(title, body, time);
                list.append(link);
            });
        } catch (error) {
            status.textContent = error.message || 'Notifications could not be loaded.';
            list.replaceChildren(status);
        } finally {
            requestInProgress = false;
        }
    }

    menu.addEventListener('show.bs.dropdown', loadNotifications);
    menu.querySelector('[data-bs-toggle="dropdown"]').addEventListener('click', loadNotifications);
    window.addEventListener('firebase-push-received', loadNotifications);
    list.addEventListener('click', async (event) => {
        const link = event.target.closest('[data-notification-id]');
        if (!link || link.origin !== window.location.origin) return;
        event.preventDefault();
        try {
            const response = await fetch(`${menu.dataset.feedUrl}/${encodeURIComponent(link.dataset.notificationId)}/read`, {
                method: 'PATCH',
                headers: {
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': menu.dataset.csrfToken,
                },
            });
            const result = await response.json();
            if (!response.ok) throw new Error(result.message || 'Notification could not be marked as read.');
            window.location.assign(link.href);
        } catch (error) {
            status.textContent = error.message || 'Notification could not be opened.';
        }
    });

    readAllButton.addEventListener('click', async () => {
        readAllButton.disabled = true;
        try {
            const response = await fetch(menu.dataset.readAllUrl, {
                method: 'PATCH',
                headers: {
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': menu.dataset.csrfToken,
                },
            });
            const result = await response.json();
            if (!response.ok) throw new Error(result.message || 'Notifications could not be marked as read.');
            await loadNotifications();
        } catch (error) {
            status.textContent = error.message || 'Notifications could not be marked as read.';
        } finally {
            readAllButton.disabled = false;
        }
    });

    void loadNotifications();
    window.setInterval(loadNotifications, 5000);
    window.addEventListener('focus', loadNotifications);
    document.addEventListener('visibilitychange', () => {
        if (!document.hidden) loadNotifications();
    });
});

if (document.querySelector('[data-qr-code]')) {
    import('qrcode').then(({ default: QRCode }) => {
        document.querySelectorAll('[data-qr-code]').forEach((canvas) => {
            QRCode.toCanvas(canvas, canvas.dataset.qrCode, {
                width: 240,
                margin: 2,
                errorCorrectionLevel: 'H',
            });
        });

        document.querySelectorAll('[data-qr-download]').forEach((button) => {
            button.addEventListener('click', async () => {
                const canvas = document.querySelector(button.dataset.canvas);
                if (!canvas) return;

                const link = document.createElement('a');
                link.download = button.dataset.filename || 'table-qr-code.png';
                link.href = await QRCode.toDataURL(canvas.dataset.qrCode, {
                    width: 1000,
                    margin: 3,
                    errorCorrectionLevel: 'H',
                });
                link.click();
            });
        });
    });
}

let activeFilterRequest;

async function loadAdminContent(url, pushHistory = true) {
    activeFilterRequest?.abort();
    activeFilterRequest = new AbortController();
    const response = await fetch(url, {
        headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'text/html' },
        signal: activeFilterRequest.signal,
    });
    if (!response.ok) throw new Error('Could not load filtered results.');
    const html = await response.text();
    const parsed = new DOMParser().parseFromString(html, 'text/html');
    const nextContent = parsed.querySelector('.admin-content');
    const currentContent = document.querySelector('.admin-content');
    if (!nextContent || !currentContent) {
        window.location.assign(url);
        return;
    }

    currentContent.replaceChildren(...nextContent.childNodes);
    if (parsed.title) document.title = parsed.title;
    if (pushHistory) history.pushState({}, '', url);
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

document.addEventListener('submit', async (event) => {
    const form = event.target;
    if (!(form instanceof HTMLFormElement)) return;

    if (form.matches('[data-ajax-filter]')) {
        event.preventDefault();
        const url = new URL(form.action, window.location.href);
        new FormData(form).forEach((value, key) => {
            if (value !== '') url.searchParams.set(key, String(value));
            else url.searchParams.delete(key);
        });
        try {
            await loadAdminContent(url);
        } catch (error) {
            if (error.name !== 'AbortError') window.location.assign(url);
        }
        return;
    }

    if (!form.matches('[data-ajax-form]')) return;
    event.preventDefault();
    if (!form.reportValidity()) return;

    const submitter = event.submitter;
    const confirmation = submitter?.dataset.confirm;
    if (confirmation && !window.confirm(confirmation)) return;

    const button = submitter instanceof HTMLButtonElement ? submitter : form.querySelector('button[type="submit"], button:not([type])');
    const oldButtonText = button?.innerHTML;
    if (button) {
        button.disabled = true;
        button.innerHTML = '<span class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>Saving';
    }

    try {
        const response = await fetch(form.action, {
            method: form.method.toUpperCase(),
            body: submitter ? new FormData(form, submitter) : new FormData(form),
            headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'application/json' },
        });
        if ((response.headers.get('content-type') || '').includes('text/html')) {
            if (!response.ok) throw new Error('The request could not be completed.');
            const html = await response.text();
            const parsed = new DOMParser().parseFromString(html, 'text/html');
            const nextContent = parsed.querySelector('.admin-content');
            const currentContent = document.querySelector('.admin-content');
            if (!nextContent || !currentContent) {
                window.location.assign(response.url);
                return;
            }
            currentContent.replaceChildren(...nextContent.childNodes);
            if (parsed.title) document.title = parsed.title;
            history.pushState({}, '', response.url);
            window.scrollTo({ top: 0, behavior: 'smooth' });
            return;
        }
        const result = await response.json();
        if (!response.ok) {
            throw Object.assign(new Error(result.message || 'Please review the form.'), { details: result.errors });
        }
        if (result.redirect) {
            window.location.assign(result.redirect);
            return;
        }
        if (result.refresh === 'board') {
            const board = document.querySelector('[data-operations-board]');
            if (board) await window.refreshOperationsBoard(board);
            return;
        }
        if (result.refresh) {
            window.location.reload();
            return;
        }
        showFormMessage(form, result.message || 'Saved successfully.', false);
        form.reset();
        if (form.closest('[data-po-items]')) return;
        if (form.matches('[action*="/inventory/"][action$="/adjust"]')) {
            window.setTimeout(() => window.location.reload(), 500);
        }
    } catch (error) {
        if (error.details) {
            const details = Object.values(error.details).flat().join(' ');
            showFormMessage(form, details || error.message, true);
        } else {
            showFormMessage(form, error.message || 'The request failed.', true);
        }
    } finally {
        if (button?.isConnected) {
            button.disabled = false;
            button.innerHTML = oldButtonText;
        }
    }
});

function showFormMessage(form, message, isError) {
    let alert = form.querySelector('[data-ajax-message]');
    if (!alert) {
        alert = document.createElement('div');
        alert.dataset.ajaxMessage = '';
        form.prepend(alert);
    }
    alert.className = `alert ${isError ? 'alert-danger' : 'alert-success'}`;
    alert.setAttribute('role', 'status');
    alert.textContent = message;
}

document.addEventListener('click', async (event) => {
    const link = event.target.closest('.admin-content .pagination a');
    if (!link || link.origin !== window.location.origin) return;
    event.preventDefault();
    try {
        await loadAdminContent(link.href);
    } catch (error) {
        if (error.name !== 'AbortError') window.location.assign(link.href);
    }
});

document.addEventListener('input', (event) => {
    const input = event.target;
    if (!(input instanceof HTMLInputElement) || !input.form?.matches('[data-ajax-filter]') || input.type !== 'search') return;
    window.clearTimeout(input.form.dataset.filterTimer);
    input.form.dataset.filterTimer = window.setTimeout(() => input.form.requestSubmit(), 300);
});

document.addEventListener('change', (event) => {
    const target = event.target;
    if (target instanceof HTMLSelectElement && target.form?.matches('[data-ajax-filter]')) {
        target.form.requestSubmit();
    }
    if (target instanceof HTMLSelectElement && target.matches('[data-po-branch]')) {
        updatePurchaseOrderItemOptions(target);
    }
    if (target instanceof HTMLSelectElement && target.matches('[data-reservation-branch]')) {
        updateReservationTableOptions(target);
    }
});

function updatePurchaseOrderItemOptions(branchSelect) {
    const form = branchSelect.form;
    form?.querySelectorAll('[data-po-item]').forEach((select) => {
        let firstAvailable;
        for (const option of select.options) {
            const available = option.dataset.branch === branchSelect.value;
            option.hidden = !available;
            option.disabled = !available;
            if (available && !firstAvailable) firstAvailable = option;
        }
        if (select.selectedOptions[0]?.disabled && firstAvailable) select.value = firstAvailable.value;
    });
}

function updateReservationTableOptions(branchSelect) {
    const tableSelect = branchSelect.form?.querySelector('[data-reservation-table]');
    if (!tableSelect) return;
    for (const option of tableSelect.options) {
        if (!option.value) continue;
        option.hidden = option.dataset.branch !== branchSelect.value;
    }
    if (tableSelect.selectedOptions[0]?.hidden) tableSelect.value = '';
}

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-reservation-branch]').forEach(updateReservationTableOptions);
    document.querySelectorAll('[data-po-branch]').forEach(updatePurchaseOrderItemOptions);
});

document.addEventListener('click', (event) => {
    const addButton = event.target.closest('[data-add-po-row]');
    if (addButton) {
        const form = addButton.closest('form');
        const template = form?.querySelector('[data-po-template]');
        const container = form?.querySelector('[data-po-items]');
        if (!template || !container) return;
        const index = container.querySelectorAll('[data-po-row]').length;
        const fragment = template.content.cloneNode(true);
        fragment.querySelectorAll('[name]').forEach((field) => {
            field.name = field.name.replaceAll('__index__', String(index));
        });
        container.append(fragment);
        const branchSelect = form.querySelector('[data-po-branch]');
        if (branchSelect) updatePurchaseOrderItemOptions(branchSelect);
    }

    const removeButton = event.target.closest('[data-remove-po-row]');
    if (removeButton && !removeButton.disabled) removeButton.closest('[data-po-row]')?.remove();

    const addTender = event.target.closest('[data-add-tender]');
    if (addTender) {
        const form = addTender.closest('form');
        const container = form?.querySelector('[data-tender-list]');
        if (!container) return;
        const rows = container.querySelectorAll('[data-tender-row]');
        if (rows.length >= 5) return;
        const row = rows[0].cloneNode(true);
        const index = rows.length;
        row.querySelectorAll('[name]').forEach((field) => {
            field.name = field.name.replace(/\[\d+\]/, `[${index}]`);
            if (field.type === 'number') field.value = '';
        });
        container.append(row);
        if (rows.length + 1 >= 5) addTender.disabled = true;
    }
});

window.addEventListener('popstate', () => {
    loadAdminContent(window.location.href, false).catch(() => window.location.reload());
});