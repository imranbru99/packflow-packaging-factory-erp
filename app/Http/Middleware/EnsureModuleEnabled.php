<?php

namespace App\Http\Middleware;

use App\Support\ModuleManager;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureModuleEnabled
{
    public function __construct(protected ModuleManager $modules) {}

    public function handle(Request $request, Closure $next, string $module): Response
    {
        if (! $this->modules->enabled($module)) {
            abort(404, 'That module is switched off for this plant.');
        }

        $user = $request->user();

        if ($user && ! $user->canAccessModule($module)) {
            abort(403);
        }

        return $next($request);
    }
}
