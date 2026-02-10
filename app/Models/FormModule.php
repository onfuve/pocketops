<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FormModule extends Model
{
    protected $fillable = [
        'form_id',
        'sort_order',
        'type',
        'config',
    ];

    protected $casts = [
        'config' => 'array',
        'sort_order' => 'integer',
    ];

    public const TYPE_CUSTOM_TEXT = 'custom_text';
    public const TYPE_FILE_UPLOAD = 'file_upload';
    public const TYPE_POSTAL_ADDRESS = 'postal_address';
    public const TYPE_CONSENT = 'consent';
    public const TYPE_SURVEY = 'survey';
    public const TYPE_CUSTOM_FIELDS = 'custom_fields';

    public static function typeLabels(): array
    {
        return [
            self::TYPE_CUSTOM_TEXT => 'متن توضیحات',
            self::TYPE_FILE_UPLOAD => 'آپلود فایل',
            self::TYPE_POSTAL_ADDRESS => 'آدرس پستی',
            self::TYPE_CONSENT => 'رضایت / تأیید',
            self::TYPE_SURVEY => 'نظرسنجی',
            self::TYPE_CUSTOM_FIELDS => 'فیلدهای سفارشی',
        ];
    }

    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }

    public function getConfig(string $key, $default = null)
    {
        return data_get($this->config, $key, $default);
    }
}
