<?php

namespace App\Helpers;

use Carbon\Carbon;
use DateTimeInterface;

/**
 * ฟังก์ชันช่วยจัดรูปแบบวันเวลาให้เป็นภาษาไทย ใช้ร่วมกันได้ทุกจุดในระบบ
 * ที่ต้องแสดงวันที่แบบไทย (เช่น ตาราง user, log, รายงาน ฯลฯ)
 */
class DateHelper
{
    /**
     * แปลงวันเวลาเป็นรูปแบบไทย (เช่น "15 มกราคม 2568 14:30") ด้วย Carbon
     *
     * - locale('th') ทำให้ชื่อเดือนเป็นภาษาไทย
     * - ->addYears(543) แปลง ค.ศ. เป็น พ.ศ. (ปีไทย) เพราะ translatedFormat
     *   ของ Carbon ไม่แปลงปีให้อัตโนมัติ ต้องปรับปีเองก่อน format
     * - คืน null ถ้าไม่มีค่า (เช่น user ที่ยังไม่เคย login จะไม่มี lastvisit_at)
     * - รับได้ทั้ง string และ DateTimeInterface (Carbon/Illuminate\Support\Carbon)
     *   เพราะ attribute ของ Eloquent model ที่ cast เป็น datetime (เช่น
     *   created_at) จะเป็น Carbon object อยู่แล้ว ไม่ใช่ string ดิบ
     *
     * @param  string|DateTimeInterface|null  $date  ค่าวันเวลาต้นฉบับ
     * @param  string  $format  รูปแบบ format ของ Carbon (default: วัน เดือน(ชื่อ) ปี ชั่วโมง:นาที)
     */
    public static function formatThaiDate(string|DateTimeInterface|null $date, string $format = 'd F Y H:i'): ?string
    {
        if (empty($date)) {
            return null;
        }

        return Carbon::parse($date)
            ->locale('th')
            ->addYears(543)
            ->translatedFormat($format);
    }
}
