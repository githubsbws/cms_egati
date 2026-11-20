<?php

namespace App\Models;

use App\Helpers\MenuLabel;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class AdminMenu extends Model
{
    protected $table = 'admin_menus';

    protected $fillable = [
        'menu_name',
        'active',
        'sort_order',
    ];

    protected $casts = [
        'active' => 'string',
    ];

    /**
     * ตารางนี้เก็บ active เป็น Y/N (char(1)) ไม่ใช่ boolean จริงใน DB
     * ให้ accessor/mutator นี้แปลงเป็น bool ให้ใช้งานสะดวกในโค้ด PHP
     */
    protected function isActive(): bool
    {
        return $this->active === 'y';
    }

    public function getIsActiveAttribute(): bool
    {
        return $this->isActive();
    }

    public function setIsActiveAttribute(bool $value): void
    {
        $this->attributes['active'] = $value ? 'y' : 'n';
    }

    /**
     * Scope: ดึงเฉพาะเมนูที่เปิดใช้งาน (active = 'Y')
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('active', 'y');
    }

    /**
     * Menu label for the current language, found by this menu's `key` in config/admin_menu.php.
     * Falls back to the menu_name column when no translation exists.
     */
    public function getLabelAttribute(): string
    {
        $key = collect(config('admin_menu'))->firstWhere('id', $this->id)['key'] ?? null;

        return $key !== null ? MenuLabel::for($key, $this->menu_name) : $this->menu_name;
    }
}
