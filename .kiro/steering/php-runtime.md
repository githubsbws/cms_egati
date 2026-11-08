---
inclusion: always
---

# PHP Runtime สำหรับโปรเจกต์นี้

เครื่องนี้มี PHP หลายเวอร์ชันติดตั้งอยู่ (`php` ใน PATH ชี้ไปที่ PHP 8.2.12 ซึ่ง**ไม่ตรง**กับ requirement ของโปรเจกต์)

โปรเจกต์นี้ต้องการ PHP **>= 8.3** (composer.json) และ dev/UAT/Prod ใช้จริงที่ **PHP 8.5** (ตาม requirements.md/design.md)

## กฎ

ทุกครั้งที่ต้องรันคำสั่ง `php` หรือ `php artisan ...` ในโปรเจกต์นี้ (composer, artisan, php -v, php -l ฯลฯ) ให้ใช้ path เต็มนี้แทนคำว่า `php` เปล่าๆ:

```
C:\php-8.5.10\php.exe
```

ตัวอย่าง:

```powershell
C:\php-8.5.10\php.exe artisan migrate
C:\php-8.5.10\php.exe artisan route:list
C:\php-8.5.10\php.exe -v
```

หากต้องรัน Composer ก็ให้สั่งผ่าน PHP ตัวนี้เช่นกัน เพื่อให้ resolve platform requirement (`php": "^8.3"`) ถูกต้อง:

```powershell
C:\php-8.5.10\php.exe C:\path\to\composer.phar install
```

หรือถ้าใช้ `composer` แบบ global ให้เช็คว่า composer เองใช้ PHP ตัวไหนรันอยู่ก่อน (composer -v) ถ้าไม่ตรงให้แจ้งผู้ใช้แทนที่จะเดา

**ห้าม** ใช้คำสั่ง `php` เปล่าๆ (ที่ resolve จาก PATH) กับโปรเจกต์นี้ เพราะจะได้ PHP 8.2 ซึ่งต่ำกว่า minimum requirement และจะพบ error แบบ:

```
Composer detected issues in your platform: Your Composer dependencies require a PHP version ">= 8.4.1"...
```
