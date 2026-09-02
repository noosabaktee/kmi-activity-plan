<?php

namespace App\Models\Concerns;

use Illuminate\Support\Facades\Schema;

trait GeneratesIntegerIds
{
    protected static function bootGeneratesIntegerIds(): void
    {
        static::creating(function ($model) {
            $keyName = $model->getKeyName();

            if (empty($model->{$keyName})) {
                $model->{$keyName} = ((int) static::max($keyName)) + 1;
            }

            if (! isset($model->dtmInserted)) {
                $model->dtmInserted = now();
            }

            if (! isset($model->txtInsertedBy)) {
                $model->txtInsertedBy = session('auth_user_name') ?: 'System';
            }

            if (array_key_exists('bitActive', $model->getAttributes()) && $model->bitActive === null) {
                $model->bitActive = true;
            }
        });

        static::updating(function ($model) {
            if (array_key_exists('dtmUpdated', $model->getAttributes())) {
                $model->dtmUpdated = now();
            }
            if (array_key_exists('txtUpdatedBy', $model->getAttributes()) && empty($model->txtUpdatedBy)) {
                $model->txtUpdatedBy = session('auth_user_name') ?: 'System';
            }
        });
    }
}
