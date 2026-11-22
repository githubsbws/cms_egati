// resources/js/admin.js
// Entry ของหลังบ้าน (admin) - AdminLTE v4 (ESM, ไม่มี jQuery) + Bootstrap JS bundle
// TinyMCE หลดแบบ dynamic import เพาะหน้าที่มีฟอรม rich text

import * as bootstrap from 'bootstrap';
import 'admin-lte';

window.bootstrap = bootstrap;

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach((el) => new bootstrap.Tooltip(el));
    document.querySelectorAll('[data-bs-toggle="popover"]').forEach((el) => new bootstrap.Popover(el));

    // ปุ่มแสดง/ซ่อนรหัสผ่าน (เช่นในหน้า admin.auth.login)
    document.querySelectorAll('[data-password-toggle]').forEach((button) => {
        const input = document.querySelector(button.dataset.passwordToggle);
        if (!input) {
            return;
        }

        button.addEventListener('click', () => {
            const showing = input.type === 'text';
            input.type = showing ? 'password' : 'text';
            button.querySelector('i')?.classList.toggle('bi-eye', showing);
            button.querySelector('i')?.classList.toggle('bi-eye-slash', !showing);
        });
    });

    if (document.querySelector('.rich-editor')) {
        import('tinymce/tinymce').then(async ({ default: tinymce }) => {
            await import('tinymce/icons/default');
            await import('tinymce/themes/silver');
            await import('tinymce/models/dom');
            await import('tinymce/plugins/link');
            await import('tinymce/plugins/lists');
            await import('tinymce/plugins/table');
            await import('tinymce/plugins/image');
            await import('tinymce/plugins/code');

            tinymce.init({
                selector: '.rich-editor',
                license_key: 'gpl',
                height: 420,
                menubar: false,
                // Skin files are copied to public/tinymce (copied from node_modules/tinymce/skins), loaded by URL
                skin_url: '/tinymce/skins/ui/oxide',
                content_css: ['/tinymce/skins/content/default/content.min.css', '/tinymce/noto-sans-thai.css'],
                content_style: "body { font-family: 'Noto Sans Thai', system-ui, sans-serif; }",
                branding: false,
                promotion: false,
                // Images inserted in the editor are uploaded to the feature folder (data-upload-feature on the textarea)
                automatic_uploads: true,
                images_upload_handler: (blobInfo, progress) => new Promise((resolve, reject) => {
                    const body = new FormData();
                    body.append('file', blobInfo.blob(), blobInfo.filename());
                    body.append('feature', document.querySelector('.rich-editor')?.dataset.uploadFeature ?? '');
                    fetch('/admin/editor/image', {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '', Accept: 'application/json' },
                        body,
                        credentials: 'same-origin',
                    })
                        .then((response) => (response.ok ? response.json() : Promise.reject(new Error('Upload failed (' + response.status + ')'))))
                        .then((json) => resolve(json.location))
                        .catch((error) => reject({ message: error.message, remove: true }));
                }),                relative_urls: false,
                convert_urls: false,
                plugins: 'link lists table image code',
                toolbar: 'undo redo | blocks | bold italic | bullist numlist | link image table | code',
            });
        });
    }
});
