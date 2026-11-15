<?php

namespace App\Models;

use App\Modules\Hr\Models\Department;
use App\Modules\Hr\Models\Shift;
use App\Modules\Crm\Models\Customer;
use App\Support\ModuleManager;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
        'employee_code',
        'designation',
        'locale',
        'is_active',
        'department_id',
        'shift_id',
        'customer_id',
        'preferences',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'preferences' => 'array',
        ];
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function isStaff(): bool
    {
        return $this->is_active && $this->role !== 'customer';
    }

    public function canAccessModule(string $module): bool
    {
        if (! app(ModuleManager::class)->enabled($module)) {
            return false;
        }

        if (in_array($this->role, ['super_admin', 'factory_manager'], true)) {
            return true;
        }

        return in_array($module, config('packflow.role_modules.'.$this->role, []), true);
    }
}
