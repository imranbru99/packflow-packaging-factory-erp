<?php

namespace App\Providers;

use App\Admin\ResourceRegistry;
use App\Models\User;
use App\Modules\Crm\Events\SalesOrderConfirmed;
use App\Modules\Crm\Models\SalesOrder;
use App\Modules\Finance\Models\Invoice;
use App\Modules\Production\Events\StageCompleted;
use App\Modules\Production\Listeners\NotifyNextStage;
use App\Modules\Production\Listeners\PlanProduction;
use App\Modules\Production\Models\ProductionOrder;
use App\Modules\Settings\Models\CompanySetting;
use App\Policies\InvoicePolicy;
use App\Policies\ProductionOrderPolicy;
use App\Policies\SalesOrderPolicy;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use ImranDevBD\LaravelAdminPanel\Facades\AdminPanel;
use ImranDevBD\LaravelAdminPanel\Navigation\NavigationGroup;
use ImranDevBD\LaravelAdminPanel\Navigation\NavigationItem;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(\App\Support\ModuleManager::class);

        // Drutogoti flushes captured queries when the process ends. Before its
        // tables exist that flush throws and aborts artisan (key:generate, the
        // first migrate). Disable it until the tables are there.
        $this->app->booting(function (): void {
            try {
                if (! \Illuminate\Support\Facades\Schema::hasTable('drutogoti_requests')) {
                    config(['drutogoti.enabled' => false]);
                }
            } catch (\Throwable) {
                config(['drutogoti.enabled' => false]);
            }
        });
    }

    public function boot(): void
    {
        Gate::before(fn (User $user) => $user->role === 'super_admin' ? true : null);
        Gate::policy(SalesOrder::class, SalesOrderPolicy::class);
        Gate::policy(ProductionOrder::class, ProductionOrderPolicy::class);
        Gate::policy(Invoice::class, InvoicePolicy::class);

        Event::listen(SalesOrderConfirmed::class, PlanProduction::class);
        Event::listen(StageCompleted::class, NotifyNextStage::class);

        View::composer('public.*', function ($view): void {
            $view->with('company', CompanySetting::current());
        });

        $this->bootNavigation();
    }

    protected function bootNavigation(): void
    {
        $panel = AdminPanel::getPanel('admin');
        $panel->brandName('PackFlow')->theme('emerald');

        $groups = [
            NavigationGroup::make('Floor')->items([
                NavigationItem::make('Overview')->icon('layout-dashboard')->url(url('/admin')),
                NavigationItem::make('Shop floor')->icon('package')->url(url('/admin/shop-floor'))
                    ->visible(fn () => auth()->user()?->canAccessModule('shopfloor') ?? false),
                NavigationItem::make('Estimate')->icon('sparkles')->url(url('/admin/estimate'))
                    ->visible(fn () => auth()->user()?->canAccessModule('crm') ?? false),
                NavigationItem::make('Reports')->icon('folder')->url(url('/admin/reports'))
                    ->visible(fn () => auth()->user()?->canAccessModule('reports') ?? false),
                NavigationItem::make('Attendance')->icon('users')->url(url('/admin/attendance'))
                    ->visible(fn () => auth()->user()?->canAccessModule('hr') ?? false),
            ]),
        ];

        foreach (ResourceRegistry::groups() as $module => $label) {
            $items = [];
            foreach (ResourceRegistry::modules() as $slug => $slugModule) {
                if ($slugModule !== $module) {
                    continue;
                }
                $items[] = NavigationItem::make(ResourceRegistry::label($slug))
                    ->icon('package')
                    ->url(url('/admin/resources/'.$slug))
                    ->visible(function () use ($module) {
                        return auth()->user()?->canAccessModule($module) ?? false;
                    });
            }
            $groups[] = NavigationGroup::make($label)->items($items)->visible(function () use ($module) {
                return auth()->user()?->canAccessModule($module) ?? false;
            });
        }

        $groups[] = NavigationGroup::make('System')->items([
            NavigationItem::make('Modules')->icon('settings')->url(url('/admin/modules'))
                ->visible(fn () => auth()->user()?->canAccessModule('settings') ?? false),
            NavigationItem::make('Public site')->icon('arrow-up-right')->url(url('/')),
            NavigationItem::make('Sign out')->icon('log-out')->url(url('/logout')),
        ]);

        $panel->navigation($groups);
    }
}
