<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\HasApiTokens;
use Laravel\Sanctum\NewAccessToken;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * فیلدهایی که نباید mass-assign بشن
     */
    protected $guarded = ['id'];

    /**
     * فیلدهایی که در JSON/Array نمایش داده نمی‌شن
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * کاست کردن فیلدها به نوع مناسب
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'banned_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | متدهای کمکی
    |--------------------------------------------------------------------------
    */

    /**
     * آیا کاربر بن شده است؟
     */
    public function isBanned(): bool
    {
        return !is_null($this->banned_at);
    }

    /**
     * بن کردن کاربر
     */
    public function ban(): void
    {
        $this->update(['banned_at' => now()]);
    }

    /**
     * آزاد کردن کاربر از بن
     */
    public function unban(): void
    {
        $this->update(['banned_at' => null]);
    }

    /**
     * آیا ایمیل کاربر تأیید شده؟
     */
    public function isEmailVerified(): bool
    {
        return !is_null($this->email_verified_at);
    }

    /**
     * ساختن توکن احراز هویت Sanctum
     */
    public function createAuthToken(): NewAccessToken
    {
        return $this->createToken('auth_token');
    }

    /**
     * بررسی صحت رمز عبور
     */
    public function checkPassword($password): bool
    {
        return !is_null($this->password) && Hash::check($password, $this->password);
    }

    /*
    |--------------------------------------------------------------------------
    | فیلدهای اختصاصی برای Microsoft Login
    |--------------------------------------------------------------------------
    | microsoft_oid : شناسه یکتای کاربر در Azure AD
    | roles         : می‌توانی یک ستون JSON اضافه کنی برای ذخیره نقش‌ها
    |--------------------------------------------------------------------------
    */
}
