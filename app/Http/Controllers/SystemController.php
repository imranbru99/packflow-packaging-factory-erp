<?php

namespace App\Http\Controllers;

use App\Modules\Settings\Models\ModuleSetting;
use App\Support\ModuleManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use ImranDevBd\AttendanceHub\Models\AttendanceDevice;
use ImranDevBd\AttendanceHub\Models\AttendanceLog;

class SystemController extends Controller
{
    public function modules(ModuleManager $modules): View
    {
        abort_unless(auth()->user()->canAccessModule('settings'), 403);

        return view('admin.modules', ['modules' => $modules->all()]);
    }

    public function updateModules(Request $request, ModuleManager $modules): RedirectResponse
    {
        abort_unless(auth()->user()->role === 'super_admin', 403);

        foreach (config('packflow.modules') as $key => $meta) {
            ModuleSetting::query()->updateOrCreate(
                ['key' => $key],
                ['label' => $meta['label'], 'enabled' => $request->boolean('modules.'.$key)]
            );
            $modules->forget($key);
        }

        return back()->with('status', 'Module switches saved.');
    }

    public function attendance(): View
    {
        abort_unless(auth()->user()->canAccessModule('hr'), 403);

        return view('admin.attendance', [
            'devices' => AttendanceDevice::query()->latest()->get(),
            'logs' => AttendanceLog::query()->latest('punched_at')->limit(30)->get(),
        ]);
    }
}
