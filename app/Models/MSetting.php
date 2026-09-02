<?php

namespace App\Models;

use App\Models\Concerns\GeneratesIntegerIds;
use Illuminate\Database\Eloquent\Model;

class MSetting extends Model
{
    use GeneratesIntegerIds;

    protected $table = 'mSetting';
    protected $primaryKey = 'intSetting_ID';
    public $incrementing = false;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'intSetting_ID',
        'txtSettingKey',
        'txtSettingValue',
        'txtSettingGroup',
        'txtDescription',
        'txtInsertedBy',
        'dtmInserted',
        'txtUpdatedBy',
        'dtmUpdated',
    ];

    protected function casts(): array
    {
        return [
            'intSetting_ID' => 'integer',
            'dtmInserted' => 'datetime',
            'dtmUpdated' => 'datetime',
        ];
    }

    /**
     * Get a setting value by key.
     */
    public static function get(string $key, ?string $default = null): ?string
    {
        $setting = static::where('txtSettingKey', $key)->first();

        return $setting && $setting->txtSettingValue !== null ? $setting->txtSettingValue : $default;
    }

    /**
     * Set a setting value by key.
     */
    public static function set(string $key, ?string $value, string $group = 'general', ?string $description = null, ?string $user = null): static
    {
        $setting = static::where('txtSettingKey', $key)->first();
        $now = now();

        if ($setting) {
            $setting->update([
                'txtSettingValue' => $value,
                'txtSettingGroup' => $group,
                'txtDescription' => $description ?? $setting->txtDescription,
                'txtUpdatedBy' => $user ?? auth()->user()?->txtEmail ?? 'system',
                'dtmUpdated' => $now,
            ]);

            return $setting;
        }

        return static::create([
            'txtSettingKey' => $key,
            'txtSettingValue' => $value,
            'txtSettingGroup' => $group,
            'txtDescription' => $description,
            'txtInsertedBy' => $user ?? auth()->user()?->txtEmail ?? 'system',
            'dtmInserted' => $now,
        ]);
    }
}
