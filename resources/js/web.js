// resources/js/web.js
// Entry ของหน้าบ้าน (public site) — Bootstrap JS bundle (มี Popper) เท่านั้น
// ไม่มี jQuery, ไม่มี Alpine.js (ตัดออกแล้ว ด design.md Decision Log)

import * as bootstrap from 'bootstrap';

// เปิดใช้ tooltip/popover ของ Bootstrap อัตนมัติถ้ามีใน DOM (ตัวอย่างแนวทาง ปรับตามจริง)
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach((el) => new bootstrap.Tooltip(el));
    document.querySelectorAll('[data-bs-toggle="popover"]').forEach((el) => new bootstrap.Popover(el));
});