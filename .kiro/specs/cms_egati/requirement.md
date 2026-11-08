# requirement.md — Small CMS (Laravel)

> เอกสารนี้บอกว่า "ระบบต้องทำอะไร" (what) ส่วน "ทำอย่างไร" (how) อยู่ใน `design.md`
> ถ้าสองไฟล์ขัดกัน ให้ถือ **requirement.md เป็นหลัก** แล้วแจ้งผู้ใช้ให้แก้ design.md

## 0. วิธีใช้เอกสารนี้ (สำหรับ AI ที่ทำงานต่อ)

- ทุกข้อมี ID (เช่น `FR-I18N-06`) ใช้อ้างอิงใน commit, test, และ PR เสมอ
- ระดับความสำคัญใช้ MoSCoW: **Must** = ต้องมีในรอบแรก, **Should** = ควรมีถ้าเวลาพอ, **Could** = ทำเมื่อเหลือเวลา, **Won't** = ไม่ทำรอบนี้
- ห้ามเพิ่มฟีเจอร์นอกเอกสารนี้เอง ถ้าเห็นว่าควรเพิ่มให้เสนอเป็นข้อความก่อน
- ข้อที่ระบุ `[ASSUMPTION]` คือสมมติฐานที่ยังไม่ได้ยืนยัน ให้ถามผู้ใช้ก่อนลงมือถ้ากระทบงานมาก (สรุปไว้ที่หัวข้อ 9)

## 1. ภาพรวมระบบ

CMS ขนาดเล็กสำหรับเว็บไซต์องค์กร ประกอบด้วย 2 ส่วน

1. **หน้าบ้าน (Public site)** แสดงเนื้อหาให้ผู้เยี่ยมชม รองรับหลายภาษา
2. **หลังบ้าน (Admin panel)** สำหรับผู้ดูแลล็อกอินเข้ามาจัดการเนื้อหา ผู้ใช้ ภาษา และการตั้งค่า

จุดเด่นที่ลูกค้าต้องการคือ **ระบบหลายภาษาที่ขยายได้**: เริ่มที่ 2 ภาษา แต่ต้อง *เพิ่มภาษาใหม่ได้โดยไม่ต้องแก้โค้ด* ด้วยการ import ไฟล์ localization

### Tech stack (กำหนดแล้ว)

| ส่วน | เทคโนโลยี |
|---|---|
| Framework | Laravel 13 |
| Language | PHP 8.5 |
| Database | PostgreSQL |
| Web server | Apache (ดู `design.md` หัวข้อ Deployment) |
| UI หลังบ้าน | AdminLTE v4 (Bootstrap 5) + Blade (ไม่ใช้ Livewire) |
| UI หน้าบ้าน | Blade + Bootstrap 5 (ไม่ใช้ Livewire, ไม่ใช้ Tailwind/Alpine) |

### ภาษาเริ่มต้น `[ASSUMPTION]`

- `th` (ไทย) เป็นภาษา default, `en` (อังกฤษ) เป็นภาษาที่สอง

## 2. ผู้ใช้และสิทธิ์ (Roles)

| Role | ทำอะไรได้ |
|---|---|
| **Admin** | ทุกอย่าง: ผู้ใช้, ภาษา/localization, ตั้งค่าเว็บ, เนื้อหา, สื่อ, เมนู, audit log |
| **Editor** | จัดการเนื้อหา (Pages, Posts), Media, Menus เท่านั้น ห้ามเข้าส่วนผู้ใช้/ภาษา/ตั้งค่า |
| **Visitor** | ผู้เยี่ยมชมหน้าบ้าน ไม่ต้องล็อกอิน |

## 3. Functional Requirements

### 3.1 Authentication และการจัดการผู้ใช้ (AUTH)

| ID | ข้อกำหนด | Priority |
|---|---|---|
| FR-AUTH-01 | ล็อกอินด้วย email + password ที่ `/admin/login` | Must |
| FR-AUTH-02 | ล็อกเอาต์ และทำลาย session ทั้งหมดของ request นั้น | Must |
| FR-AUTH-03 | ลืมรหัสผ่าน: ส่งลิงก์ reset ทางอีเมล (token มีอายุ จำกัดการใช้ครั้งเดียว) | Must |
| FR-AUTH-04 | จำกัดความพยายามล็อกอินผิด (rate limit ต่อ email+IP) และแสดงข้อความไม่บอกว่า email มีอยู่หรือไม่ | Must |
| FR-AUTH-05 | ผู้ใช้เปลี่ยนรหัสผ่านและแก้โปรไฟล์ตัวเองได้ (ต้องยืนยันรหัสผ่านเดิมก่อนเปลี่ยนรหัสผ่าน) | Must |
| FR-AUTH-06 | Admin จัดการผู้ใช้: สร้าง/แก้ไข/ปิดใช้งาน/ลบ (soft delete)/กำหนด role | Must |
| FR-AUTH-07 | ป้องกันการล็อกตัวเอง: ห้ามลบ/ปิดใช้งาน/ลด role ของ Admin คนสุดท้าย และห้ามลบตัวเอง | Must |
| FR-AUTH-08 | Session หมดอายุเมื่อไม่ใช้งานตามเวลาที่กำหนด (ค่าเริ่มต้น 120 นาที) | Must |
| FR-AUTH-09 | นโยบายรหัสผ่าน: ความยาวขั้นต่ำ 12 ตัว, ตรวจกับรายการรหัสที่รั่วไหล (uncompromised) | Should |
| FR-AUTH-10 | 2FA แบบ TOTP สำหรับบัญชี Admin | Could |

**Acceptance criteria หลัก**
- ล็อกอินผิด 5 ครั้งใน 1 นาที (ต่อ email+IP) → ได้ HTTP 429 และข้อความรอสักครู่ (FR-AUTH-04)
- ผู้ใช้ที่ `is_active = false` ล็อกอินไม่ได้ และ session เดิมถูกตัดทันทีที่ request ถัดไป (FR-AUTH-06)
- Editor เข้า `/admin/users` → HTTP 403 (FR-AUTH-06)

### 3.2 ระบบหลายภาษา (I18N) — หัวใจของงาน

ระบบแบ่งข้อความเป็น **2 ประเภท** ซึ่งต้องจัดการต่างกัน

- **UI strings**: ข้อความที่เป็นส่วนของระบบ เช่น ปุ่ม เมนูคงที่ ข้อความ validation ข้อความ error
- **Content translations**: เนื้อหาที่ผู้ดูแลเขียนเอง เช่น หน้าเพจ บทความ ป้ายชื่อเมนู

| ID | ข้อกำหนด | Priority |
|---|---|---|
| FR-I18N-01 | เก็บรายการภาษาในฐานข้อมูล: code (เช่น `th`, `en`, `ja`), ชื่ออังกฤษ, ชื่อในภาษานั้น, ทิศทางตัวอักษร (ltr/rtl), เปิด/ปิดใช้งาน, ลำดับ | Must |
| FR-I18N-02 | Admin เพิ่ม/แก้ไข/เปิด-ปิด/จัดลำดับภาษาได้จากหลังบ้าน | Must |
| FR-I18N-03 | ต้องมีภาษา default เสมอ 1 ภาษา และห้ามปิดหรือลบภาษา default | Must |
| FR-I18N-04 | UI strings เก็บในฐานข้อมูล แก้ไขได้จากหลังบ้านโดยไม่ต้อง deploy (ค้นหา/กรองตามกลุ่ม/กรองเฉพาะคีย์ที่ยังไม่แปล) | Must |
| FR-I18N-05 | **Import ไฟล์ localization** เพื่อเพิ่มภาษาใหม่หรืออัปเดตคำแปลของภาษาเดิม (รูปแบบไฟล์ดู `design.md` หัวข้อ 6) | Must |
| FR-I18N-06 | **Export ไฟล์ localization** ของภาษาใดก็ได้ รวมถึงไฟล์ "template" จากภาษา default ให้ผู้แปลนำไปแปลต่อ | Must |
| FR-I18N-07 | Import ต้องมีขั้น **Preview (dry-run)** ก่อนบันทึกจริง แสดงจำนวนคีย์ที่เพิ่ม/อัปเดต/ไม่เปลี่ยน, คีย์ที่ขาด, คีย์ที่ไม่รู้จัก, และ error | Must |
| FR-I18N-08 | Import เป็น all-or-nothing: ถ้ามี error ร้ายแรงแม้ 1 รายการ ต้องไม่บันทึกอะไรเลย | Must |
| FR-I18N-09 | ตรวจสอบไฟล์ import: ชนิดไฟล์, ขนาด, โครงสร้าง, ความยาวค่า, และ **placeholder ต้องตรงกับภาษา default** (เช่น `:name`, `:count`) | Must |
| FR-I18N-10 | เก็บประวัติการ import (ใคร เมื่อไหร่ ไฟล์อะไร ผลลัพธ์) | Should |
| FR-I18N-11 | เนื้อหา (Pages, Posts, ป้ายเมนู) แปลได้ต่อภาษา ฟอร์มแก้ไขใช้แท็บสลับภาษา | Must |
| FR-I18N-12 | URL หน้าบ้านมี prefix ภาษา `/{locale}/...` และ `/` redirect ไปภาษา default | Must |
| FR-I18N-13 | Slug แยกตามภาษา (เช่น `/th/เกี่ยวกับเรา` หรือ `/en/about-us`) ต้องไม่ซ้ำกันภายในภาษาเดียวกัน | Must |
| FR-I18N-14 | ตัวสลับภาษาที่หน้าบ้าน: ถ้าหน้านั้นมีคำแปลในภาษาปลายทางให้ลิงก์ไปหน้านั้น ถ้าไม่มีให้ไปหน้าแรกของภาษานั้น | Must |
| FR-I18N-15 | ใส่ `<html lang>`, `dir`, และ `hreflang` ให้ถูกต้องเพื่อ SEO | Must |
| FR-I18N-16 | ตัวชี้วัดความครบของคำแปลต่อภาษา (% ของ UI strings ที่แปลแล้ว) | Should |
| FR-I18N-17 | รองรับ UI แบบ RTL เต็มรูปแบบ (ภาษาอาหรับ/ฮีบรู) | Could |
| FR-I18N-18 | คีย์ที่ไม่มีคำแปลในภาษาที่ขอ ให้ fallback ไปภาษา default (เฉพาะ UI strings) | Must |
| FR-I18N-19 | สลับภาษา UI หลังบ้านได้ทุกหน้าผ่าน route `/lang/{locale}` (ใช้ session) โดยรับเฉพาะภาษา active ในฐานข้อมูล และจำค่าไว้ใน profile ผู้ใช้ | Must |

**Acceptance criteria หลัก**
- Import ไฟล์ `ja.json` ที่ถูกต้อง → มีภาษา `ja` ปรากฏในรายการภาษา และตัวสลับภาษาแสดง `日本語` (เมื่อเปิดใช้งาน) โดยไม่ต้อง deploy หรือแก้โค้ด (FR-I18N-05)
- ไฟล์ที่มี placeholder ไม่ตรง เช่น ต้นฉบับ `:name` แต่ไฟล์ใช้ `:nome` → Preview แสดง error ระบุคีย์ และปุ่มยืนยัน import ถูกปิด (FR-I18N-09)
- ไฟล์ที่เป็น PHP (`.php`) หรือชนิดอื่นนอกที่รองรับ → ปฏิเสธทันที ไม่มีการ execute ไฟล์ (FR-I18N-09)
- ปิดภาษา `en` แล้ว `/en/...` ตอบ 404 และ `en` หายจากตัวสลับภาษา (FR-I18N-02)
- เปิดหน้าที่ไม่มีคำแปลภาษา `en` ที่ `/en/about-us` → 404 (ไม่แสดงเนื้อหาภาษาอื่นปนกัน) (FR-I18N-14) `[ASSUMPTION]`

### 3.3 จัดการเนื้อหา (CONTENT)

| ID | ข้อกำหนด | Priority |
|---|---|---|
| FR-PAGE-01 | Pages: สร้าง/แก้ไข/ลบ (soft delete)/ค้นหา/กรองตามสถานะ | Must |
| FR-PAGE-02 | ฟิลด์ต่อภาษา: title, slug, content (rich text), SEO title, SEO description | Must |
| FR-PAGE-03 | สถานะ: Draft / Published (ตั้งเวลาเผยแพร่ล่วงหน้าได้ → Should) | Must |
| FR-PAGE-04 | กำหนดหน้าแรก (homepage) ได้ 1 หน้า | Must |
| FR-POST-01 | Posts (ข่าว/บทความ): CRUD, รูปปก, วันที่เผยแพร่, สถานะ Draft/Published | Must |
| FR-POST-02 | ฟิลด์ต่อภาษา: title, slug, excerpt, content, SEO fields | Must |
| FR-POST-03 | หมวดหมู่ (Categories) ที่แปลได้ | Should |
| FR-MEDIA-01 | อัปโหลดรูปภาพ (jpg, png, webp, gif) และไฟล์เอกสาร (pdf) พร้อมตรวจชนิดไฟล์จริง | Must |
| FR-MEDIA-02 | Media library: ดูรายการ ค้นหา ลบ ใส่ alt text ต่อภาษา | Must |
| FR-MEDIA-03 | สร้าง thumbnail/ปรับขนาดรูปอัตโนมัติ | Should |
| FR-MENU-01 | จัดการเมนูหน้าบ้าน (อย่างน้อย header และ footer) ลำดับและซ้อนได้ 2 ระดับ | Must |
| FR-MENU-02 | รายการเมนูลิงก์ไปยัง Page, Post หรือ URL ภายนอก และป้ายชื่อแปลได้ | Must |
| FR-SET-01 | ตั้งค่าเว็บ: ชื่อเว็บ (แปลได้), โลโก้, favicon, อีเมลติดต่อ, ข้อความ footer (แปลได้) | Must |
| FR-LOG-01 | Audit log: บันทึกการสร้าง/แก้ไข/ลบข้อมูลสำคัญ, ล็อกอิน, import localization (ใคร/ทำอะไร/เมื่อไหร่/IP) และ Admin ดูได้ | Should |

### 3.4 หน้าบ้าน (WEB)

| ID | ข้อกำหนด | Priority |
|---|---|---|
| FR-WEB-01 | หน้าแรก, หน้าเพจตาม slug, รายการ Posts (แบ่งหน้า), หน้ารายละเอียด Post | Must |
| FR-WEB-02 | แสดงเฉพาะเนื้อหาสถานะ Published และถึงเวลาเผยแพร่แล้ว | Must |
| FR-WEB-03 | Responsive (มือถือ/แท็บเล็ต/เดสก์ท็อป) | Must |
| FR-WEB-04 | SEO ทั้งหมดเป็นไปตามหัวข้อ 3.5 (FR-SEO-*) | Must |
| FR-WEB-05 | หน้า 404/500 ที่แสดงตามภาษาปัจจุบัน | Must |
| FR-WEB-06 | ค้นหาเนื้อหาในเว็บ (PostgreSQL full-text) | Could |
| FR-WEB-07 | ฟอร์มติดต่อพร้อมระบบป้องกัน spam | Could |

### 3.5 SEO (มีใน TOR — ต้องทำให้ดี)

> รายละเอียดตัวเลขเป้าหมายต้องยืนยันกับ TOR ฉบับจริง ข้อที่มี `[ASSUMPTION]` เป็นค่าที่ตั้งไว้ชั่วคราว

| ID | ข้อกำหนด | Priority |
|---|---|---|
| FR-SEO-01 | หน้าบ้าน render HTML เต็มเนื้อหาฝั่ง server (ไม่พึ่ง JavaScript เพื่อให้บอทเห็นเนื้อหา) | Must |
| FR-SEO-02 | `<title>` และ meta description ต่อหน้าต่อภาษา ถ้าไม่กรอกให้สร้างอัตโนมัติจาก title/excerpt | Must |
| FR-SEO-03 | `rel="canonical"` เป็น absolute URL ของภาษานั้น และ `hreflang` ครบทุกภาษาที่มีคำแปล (รวม self-reference, `x-default`, และต้องชี้หากันสองทาง) | Must |
| FR-SEO-04 | URL สะอาด: lowercase, ไม่มี query ที่ไม่จำเป็น, มีรูปแบบเดียวต่อหน้า (ไม่มี trailing slash ซ้ำ), redirect 301 เมื่อเข้าด้วยรูปแบบอื่น | Must |
| FR-SEO-05 | เปลี่ยน slug หรือย้ายหน้า → สร้าง redirect 301 จาก URL เก่าอัตโนมัติ | Must |
| FR-SEO-06 | Admin จัดการรายการ redirect เองได้ (เพิ่ม/แก้/ลบ) | Should |
| FR-SEO-07 | `sitemap.xml` ครบทุกภาษา เฉพาะหน้า Published และ indexable มี `lastmod` และลิงก์สลับภาษา; `robots.txt` อ้างอิง sitemap และ disallow `/admin` | Must |
| FR-SEO-08 | Open Graph + Twitter Card ต่อหน้า (title, description, image, locale) กำหนดรูปแชร์ได้ต่อหน้า | Must |
| FR-SEO-09 | Structured data (JSON-LD): Organization + WebSite ที่หน้าแรก, Article ที่หน้า Post, BreadcrumbList | Must |
| FR-SEO-10 | ตั้ง `noindex` ต่อหน้าได้; หลังบ้านทุกหน้าต้องเป็น noindex เสมอ | Must |
| FR-SEO-11 | Semantic HTML: มี `<h1>` เดียวต่อหน้า, ลำดับ heading ถูกต้อง, รูปมี `alt` (เตือนผู้ดูแลถ้ารูปใน content ไม่มี alt) | Must |
| FR-SEO-12 | HTTP status ถูกต้อง: ไม่พบ/Draft → 404, ไม่ใช้ soft-404 (หน้า error ที่ตอบ 200) | Must |
| FR-SEO-13 | Core Web Vitals อยู่ในเกณฑ์ "ดี" (ดู NFR-PERF-03) | Must |
| FR-SEO-14 | Lighthouse SEO ≥ 95 และ Performance ≥ 85 (มือถือ) บนหน้าตัวอย่างหลัก `[ASSUMPTION]` | Should |

**Acceptance criteria หลัก**
- ดู source หน้า `/th/about-us` ต้องเห็น `<title>`, description, canonical, hreflang (`th`, `en`, `x-default`) ครบ โดยไม่ต้องรัน JavaScript (FR-SEO-01..03)
- เปลี่ยน slug จาก `about-us` เป็น `about` → `/en/about-us` ตอบ 301 ไป `/en/about` ทันที (FR-SEO-05)
- Post ที่เป็น Draft เปิดตรง ๆ ได้ 404 และไม่อยู่ใน sitemap (FR-SEO-07, 12)
- ตรวจ JSON-LD ผ่านเครื่องมือตรวจ structured data โดยไม่มี error (FR-SEO-09)

## 4. Non-Functional Requirements

### 4.1 Security (ลูกค้าให้ความสำคัญเป็นพิเศษ)

| ID | ข้อกำหนด |
|---|---|
| NFR-SEC-01 | ทุก request ที่เปลี่ยนข้อมูลต้องมี CSRF protection |
| NFR-SEC-02 | รหัสผ่านเก็บด้วย hash มาตรฐานของ Laravel (bcrypt/argon2) ห้ามเก็บ plaintext หรือ log |
| NFR-SEC-03 | Authorization ทุก endpoint หลังบ้านผ่าน Policy/Gate ไม่เช็คสิทธิ์แค่ซ่อนปุ่มใน UI |
| NFR-SEC-04 | Rich text ต้อง **sanitize ฝั่ง server** (allowlist tag/attribute) ก่อนบันทึก ไม่เชื่อ output ของ editor |
| NFR-SEC-05 | UI strings เป็น plain text เท่านั้น แสดงผลด้วย escaping เสมอ (`{{ }}`) ห้ามใช้ `{!! !!}` กับข้อความที่ผู้ใช้/ไฟล์ import ใส่เข้ามา |
| NFR-SEC-06 | อัปโหลดไฟล์: ตรวจ MIME จากเนื้อไฟล์ ไม่ใช่แค่นามสกุล, จำกัดขนาด, สุ่มชื่อไฟล์ใหม่, เก็บนอก web root หรือปิดการ execute สคริปต์ในโฟลเดอร์อัปโหลด, ไม่รับ SVG (หรือ sanitize ถ้าจำเป็น) |
| NFR-SEC-07 | ไฟล์ import localization: รับเฉพาะ JSON, จำกัดขนาด/จำนวนคีย์, parse เป็นข้อมูลเท่านั้น (ห้าม `include`/`eval`/`unserialize`) |
| NFR-SEC-08 | Security headers: CSP, `X-Content-Type-Options`, `X-Frame-Options`/`frame-ancestors`, `Referrer-Policy`, HSTS (เมื่อใช้ HTTPS) |
| NFR-SEC-09 | Cookie ของ session: `Secure`, `HttpOnly`, `SameSite=Lax` (หรือเข้มกว่า) |
| NFR-SEC-10 | ใช้ Eloquent/Query Builder แบบ parameterized ห้ามต่อสตริง SQL จาก input |
| NFR-SEC-11 | `APP_DEBUG=false` บน production, ไม่เปิดเผย stack trace, `.env` อยู่นอก web root |
| NFR-SEC-12 | Dependency: รัน `composer audit` ใน CI และล็อกเวอร์ชันด้วย `composer.lock` |
| NFR-SEC-13 | ผู้ใช้ DB ของแอปมีสิทธิ์เท่าที่จำเป็น (ไม่ใช้ superuser) |

### 4.2 คุณภาพอื่น ๆ

| ID | ข้อกำหนด |
|---|---|
| NFR-PERF-01 | หน้าบ้านที่ cache แล้วตอบภายใน ~300 ms (server time) และไม่มี N+1 query ในหน้าหลัก (ตรวจด้วย test/debugbar) |
| NFR-PERF-02 | การค้นหา UI strings ใน Admin ลื่นที่ 20,000 แถว (มี index ที่เหมาะสม) |
| NFR-PERF-03 | Core Web Vitals (หน้าบ้าน ข้อมูลภาคสนาม/ห้องทดลอง): LCP ≤ 2.5 s, INP ≤ 200 ms, CLS ≤ 0.1 |
| NFR-A11Y-01 | หน้าบ้านผ่านข้อกำหนดพื้นฐาน WCAG 2.1 AA: semantic HTML, alt text, contrast, keyboard navigation |
| NFR-MAINT-01 | โค้ดตาม PSR-12 (Laravel Pint), วิเคราะห์ด้วย Larastan, ผ่านทุกครั้งก่อน merge |
| NFR-MAINT-02 | มี automated tests (Feature test เป็นหลัก) ครอบคลุม AUTH, I18N import, สิทธิ์ของแต่ละ role |
| NFR-OPS-01 | มี migration + seeder ครบ ติดตั้งใหม่ได้ด้วยคำสั่งชุดเดียว (สร้าง Admin แรก, ภาษา th/en, UI strings ตั้งต้น) |
| NFR-OPS-02 | มีขั้นตอน backup/restore ฐานข้อมูลและไฟล์อัปโหลดเป็นเอกสาร |
| NFR-COMPAT-01 | รองรับเบราว์เซอร์รุ่นปัจจุบันของ Chrome, Edge, Firefox, Safari |

## 5. ข้อมูลหลัก (Data Entities ระดับแนวคิด)

User, Language, UiTranslation, TranslationImport, Page (+PageTranslation), Post (+PostTranslation), Category (+Translation), Media (+alt ต่อภาษา), Menu (+MenuItem +Translation), Setting (+ค่าต่อภาษา), AuditLog

โครงสร้างตารางจริงอยู่ใน `design.md`

## 6. Out of Scope (Won't — รอบนี้)

- Page builder แบบลากวาง / ระบบ theme หลายชุด
- ระบบความคิดเห็น (comments), สมาชิกฝั่งผู้เยี่ยมชม, อีคอมเมิร์ซ
- แปลเนื้อหาอัตโนมัติด้วย AI / machine translation
- Workflow อนุมัติหลายขั้น, ประวัติเวอร์ชันเนื้อหา (revisions)
- Multi-tenant / หลายเว็บในระบบเดียว
- REST/GraphQL API สาธารณะ (headless)
- รองรับภาษาเขียนจากขวาไปซ้ายแบบสมบูรณ์ (เว้นแต่ยกระดับ FR-I18N-17)

## 7. Definition of Done (ต่อหนึ่งงาน/ฟีเจอร์)

1. ทำตาม requirement ที่อ้างอิง และ acceptance criteria ผ่าน
2. มี test ที่ผ่าน อย่างน้อย happy path + กรณีสิทธิ์ไม่พอ + กรณี input ผิด
3. ผ่าน Pint + Larastan
4. ไม่มี string ภาษาไทย/อังกฤษ hard-code ใน view/controller (ใช้ translation key ทั้งหมด)
5. มี migration ที่ย้อนกลับได้ (`down()` ใช้งานได้)
6. อัปเดตเอกสารหากการตัดสินใจเปลี่ยนจาก design.md

## 8. Glossary

| คำ | ความหมาย |
|---|---|
| Locale | รหัสภาษา เช่น `th`, `en`, `ja`, `zh-CN` (ใช้รูปแบบ BCP 47 แบบย่อ) |
| UI string | ข้อความของระบบที่ใช้ผ่าน translation key รูปแบบเดียวกับ Laravel เช่น `__('admin/common.search_placeholder')` โดย **group** = `admin/common` (path ไฟล์ใต้ `lang/{locale}/` ไม่รวม `.php`) และ **key** = `search_placeholder` |
| Content translation | เนื้อหาที่ผู้ดูแลเขียนแยกตามภาษา |
| Dry-run | ตรวจและแสดงผลที่จะเกิดขึ้นโดยยังไม่บันทึกจริง |
| Fallback | ใช้ภาษา default แทนเมื่อไม่มีคำแปลของภาษาที่ขอ |

## 9. สมมติฐานและคำถามที่ต้องยืนยัน (Open Questions)

| # | คำถาม | ค่าที่สมมติไว้ถ้ายังไม่มีคำตอบ |
|---|---|---|
| Q1 | ภาษา default คือไทยใช่หรือไม่ | `th` |
| Q2 | ภาษาที่เพิ่มด้วยการ import ใช้กับ **หน้าบ้านอย่างเดียว** หรือใช้กับ UI หลังบ้านด้วย | ใช้ได้ทั้งสองถ้า UI strings ครบ |
| Q3 | ไฟล์ localization ที่ลูกค้ามีอยู่แล้วเป็นรูปแบบไหน (JSON / PHP array ของ Laravel / CSV / XLIFF / PO) | JSON (ถ้าเป็นแบบอื่น ต้องมีตัวแปลงที่ปลอดภัย) |
| Q4 | เนื้อหาที่ยังไม่มีคำแปลในภาษานั้น ให้ซ่อน (404) หรือแสดงภาษา default แทน | ซ่อน (404) |
| Q5 | ต้องการ content type อื่นนอกจาก Pages/Posts หรือไม่ (เช่น สินค้า, ทีมงาน, FAQ) | ไม่ต้องการ |
| Q6 | ส่งอีเมลผ่าน SMTP ของใคร (จำเป็นสำหรับ FR-AUTH-03) | ใช้ SMTP ที่ลูกค้าให้ |
| Q7 | ต้องการ 2FA (FR-AUTH-10) ในรอบแรกหรือไม่ เนื่องจากลูกค้าเน้นความปลอดภัย | ไม่ (Could) |
| Q8 | มีโลโก้/แบบ UI (design mockup) หรือให้ออกแบบเอง | ออกแบบเรียบง่ายด้วย Bootstrap 5 |
| Q9 | ปริมาณเนื้อหา/ผู้เข้าชมโดยประมาณ | เว็บองค์กรขนาดเล็ก ไม่เกิน ~1,000 หน้า/บทความ |
| Q10 | ~~หน้าบ้านใช้ URL prefix หรือ session~~ **ตัดสินใจแล้ว**: SEO อยู่ใน TOR → หน้าบ้านใช้ URL prefix `/{locale}/...`, หลังบ้านใช้ session ผ่าน `/lang/{locale}` | — |
| Q11 | ~~รูปแบบคีย์ใน lang~~ **ยืนยันแล้ว**: ไฟล์ PHP array (`'name' => '...'`) เรียกด้วย `__('folder/file.key')` (group มี `/` ได้) | — |
| Q12 | TOR ระบุเป้าหมาย SEO ไว้ละเอียดแค่ไหน (เช่น คะแนน Lighthouse, schema ที่ต้องมี, keyword ต่อหน้า, URL structure ที่กำหนด) | ใช้ค่าใน 3.5 |
| Q13 | เว็บเดิม (ถ้ามี) มี URL ที่ต้อง redirect มาที่เว็บใหม่หรือไม่ | ไม่มี (ถ้ามี ต้องเตรียม import รายการ redirect) |
