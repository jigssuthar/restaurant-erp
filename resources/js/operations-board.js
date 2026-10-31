let operationsBoardRequest = false;

async function refreshOperationsBoard(root) {
    if (operationsBoardRequest || document.hidden) return;
    operationsBoardRequest = true;
    try {
        const response = await fetch(root.dataset.feedUrl, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'text/html' },
        });
        if (!response.ok) throw new Error('The live board could not be refreshed.');
        const html = await response.text();
        const parsed = new DOMParser().parseFromString(html, 'text/html');
        const board = parsed.querySelector('.kds-board-content, .service-board-content');
        if (!board) throw new Error('The live board returned an unexpected response.');
        root.innerHTML = html;
    } catch (error) {
        const status = root.querySelector('[aria-live="polite"]');
        if (status) status.textContent = `${error.message} Retrying shortly.`;
    } finally {
        operationsBoardRequest = false;
    }
}

window.refreshOperationsBoard = refreshOperationsBoard;

window.setInterval(() => {
    const root = document.querySelector('[data-operations-board]');
    if (root) refreshOperationsBoard(root);
}, 10000);

document.addEventListener('click', (event) => {
    const button = event.target.closest('[data-board-refresh]');
    if (!button) return;
    const root = document.querySelector('[data-operations-board]');
    if (root) refreshOperationsBoard(root);
});
