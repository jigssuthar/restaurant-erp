import './bootstrap';
import 'bootstrap/dist/js/bootstrap.bundle.min.js';

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