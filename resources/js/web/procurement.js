// Opens the procurement detail dialogs (native <dialog>).
export function initProcurement() {
    document.querySelectorAll('[data-proc-detail]').forEach((button) => {
        button.addEventListener('click', () => document.getElementById(button.dataset.procDetail)?.showModal());
    });
    document.querySelectorAll('.proc-detail-dialog').forEach((dialog) => {
        dialog.querySelector('[data-proc-close]')?.addEventListener('click', () => dialog.close());
        dialog.addEventListener('click', (event) => {
            if (event.target === dialog) {
                dialog.close();
            }
        });
    });
}