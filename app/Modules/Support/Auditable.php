<?php

namespace App\Modules\Support;

trait Auditable
{
    public static function bootAuditable(): void
    {
        static::created(function (PackflowModel $model): void {
            $model->writeAudit('created');
        });

        static::updated(function (PackflowModel $model): void {
            $model->writeAudit('updated', $model->getChanges());
        });
    }

    protected function writeAudit(string $event, array $changes = []): void
    {
        if (! \Illuminate\Support\Facades\Schema::hasTable('activity_logs')) {
            return;
        }

        unset($changes['updated_at']);

        \App\Modules\Settings\Models\ActivityLog::query()->create([
            'user_id' => auth()->id(),
            'subject_type' => $this->getMorphClass(),
            'subject_id' => $this->getKey(),
            'event' => $event,
            'properties' => $changes === [] ? null : $changes,
        ]);
    }
}
