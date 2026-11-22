// resources/js/web.js
// Front office entry. Plain JS ported from the design draft (no Bootstrap, no jQuery).
import { initHeader } from './web/header';
import { initHome } from './web/home';
import { initNews } from './web/news';

const ready = () => {
    initHeader();
    initHome();
    initNews();
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', ready);
} else {
    ready();
}