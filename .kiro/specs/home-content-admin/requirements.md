# Requirements Document

## Introduction

This feature adds a back office for admin menu id 2 (key `content`, "Home page content") so administrators can manage four sections of the public home page: the hero video, the about story, the video presentation, and the related agencies. The `landing_page_structure` child is removed. Text content is bilingual (one value per row in the `languages` table). The public home page keeps its current design and content as defaults whenever no override exists.

## Glossary

- **Admin_Panel**: The AdminLTE v4 back office of the CMS.
- **Home_Content_Module**: The set of admin pages, routes, and persistence logic introduced by this feature.
- **Menu_Permission**: The existing `menu.permission:<id>` check applied to admin menu id 2.
- **Hero_Section**: The `.video-hero` block in `web/home/content.blade.php` (menu key `top_cover`, label to be renamed, e.g. "Hero video" / "วิดีโอหน้าแรก").
- **About_Story_Section**: The 3-slide block in `web/home/about-story.blade.php` (menu key `infographic`, to be renamed, e.g. "About story" / "เรื่องราวองค์กร").
- **Slide**: One of the three fixed About_Story_Section entries (background video, poster, kicker, title, paragraph, CTA label and link, chapter button label).
- **Presentation_Section**: The block in `web/home/presentation.blade.php` (new child, "Video presentation").
- **Agency_Section**: The block in `web/home/stakeholders.blade.php` (new child, "Related agencies" / "หน่วยงานที่เกี่ยวข้อง").
- **Agency**: One related-agency entry (logo, name per language, URL, display order, active flag).
- **Home_Page**: The public front office home page.
- **Default_Content**: The text, video, and image values currently hard-coded in the Blade views, `__web()` strings, and `public/web-assets`.
- **Override**: A value saved through the Home_Content_Module that replaces the Default_Content for one field.
- **Active_Language**: A row in `languages` available for text entry.
- **Upload_Helper**: `UploadHelper::store($file, 'feature/sub')`, which stores files on the public disk.
- **HTML_Sanitizer**: `HtmlSanitizer`, which sanitizes rich text.

## Requirements

### Requirement 1: Menu structure and access

**User Story:** As an administrator, I want the Home content menu to list only the four relevant sections, so that I can reach each one directly.

#### Acceptance Criteria

1. THE Admin_Panel SHALL list exactly four children under menu id 2: Hero video (`top_cover`), About story (`infographic`), Video presentation, and Related agencies, each with a route.
2. THE Admin_Panel SHALL NOT list `landing_page_structure` under menu id 2 (sub-menus are defined in `config/admin_menu.php`, so no database change is needed).
3. THE Admin_Panel SHALL provide Thai and English labels for all four children in `lang/{th,en}/admin/menuleft.php`.
4. THE Home_Content_Module SHALL protect every route with `menu.permission:2`.
5. IF a user without permission on menu id 2 requests a Home_Content_Module route, THEN THE Admin_Panel SHALL deny access with the same response used by other menu-protected routes.
6. WHILE a user lacks permission on menu id 2, THE Admin_Panel SHALL hide the Home content menu and all its children from that user.
7. WHERE a user has permission on menu id 2, THE Home_Content_Module SHALL grant access to all four sections without further permission checks.

### Requirement 2: Hero video management

**User Story:** As an administrator, I want to edit the hero section at the top of the home page, so that the main message and video can be updated without a deployment.

#### Acceptance Criteria

1. THE Home_Content_Module SHALL provide an edit page for the Hero_Section with these fields: video file, poster image, eyebrow, heading (h1), paragraph, primary button label and URL, secondary button label and URL, caption title, caption link label, and caption link URL.
2. THE Home_Content_Module SHALL accept text fields (eyebrow, heading, paragraph, button labels, caption title, caption link label) separately for each Active_Language.
3. WHEN a valid video file is submitted, THE Home_Content_Module SHALL store it through Upload_Helper and record it as the Hero_Section video.
4. WHEN a valid poster image is submitted, THE Home_Content_Module SHALL store it through Upload_Helper and record it as the Hero_Section poster.
5. WHEN rich-text fields (heading, paragraph) are saved, THE Home_Content_Module SHALL sanitize them with HTML_Sanitizer.
6. IF a submitted video is not MP4/WebM or exceeds the configured size limit, THEN THE Home_Content_Module SHALL reject the request and show a localized validation error.
7. IF a submitted poster is not JPG/PNG/WebP or exceeds the configured size limit, THEN THE Home_Content_Module SHALL reject the request and show a localized validation error.
8. IF a submitted URL is not an http(s) URL or a site-relative path, THEN THE Home_Content_Module SHALL reject the request and show a localized validation error.
9. WHEN the Home_Page renders the Hero_Section, THE Home_Page SHALL show the Override for each field that has one and the Default_Content for each field that does not.
10. THE Home_Page SHALL render the Hero_Section text in the visitor's current language and fall back to the Default_Content when the Override for that language is empty.

### Requirement 3: About story management

**User Story:** As an administrator, I want to edit the three about-story slides, so that the videos and messages in that section can be changed.

#### Acceptance Criteria

1. THE Home_Content_Module SHALL provide an edit page listing exactly three Slides in fixed order, with no add, delete, or reorder control.
2. THE Home_Content_Module SHALL let the administrator edit these fields for each Slide: background video, poster image, kicker, title, paragraph, CTA label, CTA URL, and chapter button label.
3. THE Home_Content_Module SHALL accept kicker, title, paragraph, CTA label, and chapter button label separately for each Active_Language.
4. WHEN a valid video or poster file is submitted for a Slide, THE Home_Content_Module SHALL store it through Upload_Helper and record it for that Slide only.
5. WHEN rich-text fields (title, paragraph) are saved, THE Home_Content_Module SHALL sanitize them with HTML_Sanitizer.
6. IF a file or URL fails the validation rules in Requirement 2.6 to 2.8, THEN THE Home_Content_Module SHALL reject the request and show a localized validation error.
7. WHEN the Home_Page renders the About_Story_Section, THE Home_Page SHALL show three Slides, using the Override for each field that has one and the Default_Content for each field that does not.
8. WHEN a Slide has no poster Override, THE Home_Page SHALL use the Default_Content poster for that Slide, not the Slide's video Override.
9. THE Home_Page SHALL keep the video `data-src` lazy-loading attribute and the Slide numbering, chapter buttons, and progress behavior unchanged.

### Requirement 4: Video presentation management

**User Story:** As an administrator, I want to edit the company video presentation block, so that the video and its description stay current.

#### Acceptance Criteria

1. THE Home_Content_Module SHALL provide an edit page for the Presentation_Section with these fields: video file, poster image, eyebrow, title, description, language note, duration, and watch-button label.
2. THE Home_Content_Module SHALL accept eyebrow, title, description, language note, and watch-button label separately for each Active_Language.
3. WHEN a valid video or poster file is submitted, THE Home_Content_Module SHALL store it through Upload_Helper and record it for the Presentation_Section.
4. IF the duration is not in `mm:ss` or `hh:mm:ss` format, THEN THE Home_Content_Module SHALL reject the request and show a localized validation error.
5. IF a file fails the validation rules in Requirement 2.6 to 2.7, THEN THE Home_Content_Module SHALL reject the request and show a localized validation error.
6. WHEN rich-text fields (title, description) are saved, THE Home_Content_Module SHALL sanitize them with HTML_Sanitizer.
7. WHEN the Home_Page renders the Presentation_Section, THE Home_Page SHALL show the Override for each field that has one and the Default_Content for each field that does not.
8. THE Home_Page SHALL use the presentation video as the source of both the player and the "open original video" link.

### Requirement 5: Related agencies management

**User Story:** As an administrator, I want to add, edit, delete, and order related agencies, so that the logo grid reflects our current partners.

#### Acceptance Criteria

1. THE Home_Content_Module SHALL provide a list page showing each Agency with its logo, name in the current admin language, URL, display order, and active state.
2. WHEN an administrator creates an Agency with a logo, a URL, and a name for every Active_Language, THE Home_Content_Module SHALL store the logo through Upload_Helper and save the Agency as active.
3. WHEN an administrator edits an Agency, THE Home_Content_Module SHALL update the submitted fields and keep the existing logo when no new logo is submitted.
4. WHEN an administrator deletes an Agency, THE Home_Content_Module SHALL mark it deleted using the project's soft-delete convention and keep the row in the database.
5. WHEN an administrator toggles an Agency's active state, THE Home_Content_Module SHALL save the new state.
6. WHEN an administrator reorders Agencies, THE Home_Content_Module SHALL save the new order as consecutive integers starting at 1.
7. IF a required name, logo (on create), or URL is missing, or the URL is not http(s), or the logo is not JPG/PNG/WebP/SVG or exceeds the configured size limit, THEN THE Home_Content_Module SHALL reject the request and show a localized validation error.
8. WHEN the Home_Page renders the Agency_Section, THE Home_Page SHALL show only active, non-deleted Agencies in display order, each with its logo, URL (opening in a new tab with `rel="noopener"`), and name in the visitor's current language as tooltip and accessible label.
9. IF the Agency name for the visitor's current language is empty, THEN THE Home_Page SHALL show the name of the default language.
10. THE Home_Page SHALL keep the existing stakeholder markup, classes, and staggered reveal delay for each Agency.

### Requirement 6: Default content and migration

**User Story:** As a site owner, I want the home page to look the same until something is changed, so that deploying the feature causes no visible regression.

#### Acceptance Criteria

1. WHEN no Override exists for any Home_Content_Module field, THE Home_Page SHALL render output equivalent to the current Home_Page.
2. THE Home_Content_Module SHALL seed the six existing agencies (logo `stakeholders/1.png` to `6.png`, URL, Thai and English names) in the current display order, so that the Agency_Section looks unchanged after deployment.
3. IF the Home_Content_Module storage is unavailable or a media file referenced by an Override is missing, THEN THE Home_Page SHALL render the Default_Content for the affected field and continue to load.
4. WHEN a file Override is replaced, THE Home_Content_Module SHALL remove the previous uploaded file from storage.
5. THE Home_Content_Module SHALL record the administrator and timestamp of the last update for each section.

### Requirement 7: Admin UI conventions

**User Story:** As an administrator, I want these pages to behave like the other admin features, so that they are familiar to use.

#### Acceptance Criteria

1. THE Home_Content_Module SHALL use the AdminLTE v4 and Bootstrap 5 layout, with a language tab or panel for each Active_Language on text fields.
2. WHEN a save succeeds, THE Admin_Panel SHALL show a localized success message; WHEN a save fails validation, THE Admin_Panel SHALL keep the entered values and show errors beside the fields.
3. THE Home_Content_Module SHALL show a preview of the current video or image for each file field, marking whether it is the default or an Override.
4. THE Home_Content_Module SHALL provide Thai and English admin labels and messages in `lang/{th,en}/admin`.
5. THE Home_Content_Module SHALL validate input in Form Request classes under `app/Http/Requests/Admin`.
6. THE Home_Content_Module SHALL include feature tests that run on pgsql with DatabaseTransactions and cover access control, save, validation failure, and default rendering for each section.
