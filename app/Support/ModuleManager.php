<?php

namespace App\Support;

use App\Modules\Settings\Models\ModuleSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class ModuleManager
{
    public function enabled(string $key): bool
    {
        $defaults = config('packflow.modules.'.$key);

        if ($defaults === null || ! ($defaults['enabled'] ?? true)) {
            return false;
        }

        if (! Schema::hasTable('module_settings')) {
            return true;
        }

        $enabled = Cache::remember('packflow.module.'.$key, 30, function () use ($key, $defaults) {
            $row = ModuleSetting::query()->where('key', $key)->first();

            return $row ? (bool) $row->enabled : true;
        });

        return (bool) $enabled;
    }

    public function forget(string $key): void
    {
        Cache::forget('packflow.module.'.$key);
    }

    /** @return array<string, array{label: string, enabled: bool}> */
    public function all(): array
    {
        $modules = [];

        foreach (config('packflow.modules') as $key => $meta) {
            $modules[$key] = [
                'label' => $meta['label'],
                'enabled' => $this->enabled($key),
            ];
        }

        return $modules;
    }
}
