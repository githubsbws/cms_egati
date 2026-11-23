// resources/js/web.js
// Front office entry. Plain JS ported from the design draft (no Bootstrap, no jQuery).
import { initAbout } from './web/about';
import { initHeader } from './web/header';
import { initHome } from './web/home';
import { initNews } from './web/news';
import { initProcurement } from './web/procurement';

const ready = () => {
    initHeader();
    initHome();
    initNews();
    initAbout();
    initProcurement();
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', ready);
} else {
    ready();
}