/* About pages: zoom buttons of the company structure chart (ported from the design draft). */
export function initAbout() {
    const image = document.getElementById('ac-organization-image');
    if (!image) {
        return;
    }
    const out = document.querySelector('[data-ac-zoom="out"]');
    const zoomIn = document.querySelector('[data-ac-zoom="in"]');
    const label = document.getElementById('ac-zoom-value');
    let zoom = 100;

    const apply = () => {
        image.style.width = zoom + '%';
        label.textContent = zoom + '%';
        out.disabled = zoom === 100;
        zoomIn.disabled = zoom === 250;
    };

    document.querySelectorAll('[data-ac-zoom]').forEach((button) => {
        button.onclick = () => {
            zoom = button.dataset.acZoom === 'reset' ? 100 : Math.max(100, Math.min(250, zoom + (button.dataset.acZoom === 'in' ? 25 : -25)));
            apply();
        };
    });
    apply();
}