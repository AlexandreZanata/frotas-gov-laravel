<?php

namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

trait Auditable
{
    public static function bootAuditable()
    {
        static::created(function ($model) {
            self::logChange($model, 'create');
        });

        static::updated(function ($model) {
            self::logChange($model, 'update');
        });

        static::deleted(function ($model) {
            self::logChange($model, 'delete');
        });
    }

    protected static function logChange($model, $action)
    {
        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'table_name' => $model->getTable(),
            'record_id' => $model->getKey(),
            'old_value' => $action === 'delete' || $action === 'update' ? $model->getOriginal() : null,
            'new_value' => $action === 'create' || $action === 'update' ? $model->getDirty() : null,
            'ip_address' => request()->ip(),
        ]);
    }
}
