<?php

// Only the messages used by the admin forms so far. Missing keys fall back to English.
return [
    'required' => 'กรุณากรอก :attribute',
    'email' => ':attribute ต้องเป็นอีเมลที่ถูกต้อง',
    'string' => ':attribute ต้องเป็นข้อความ',
    'integer' => ':attribute ต้องเป็นจำนวนเต็ม',
    'array' => ':attribute ต้องเป็นอาร์เรย์',
    'exists' => ':attributeที่เลือกไม่ถูกต้อง',
    'unique' => ':attributeถูกใช้งานแล้ว',
    'confirmed' => 'การยืนยัน :attribute ไม่ตรงกัน',
    'max' => ['string' => ':attribute ต้องไม่เกิน :max ตัวอักษร'],
    'min' => ['string' => ':attribute ต้องมีอย่างน้อย :min ตัวอักษร'],
    'attributes' => [
        'email' => 'อีเมล',
        'password' => 'รหัสผ่าน',
        'group_name' => 'ชื่อกลุ่ม',
    ],
];
