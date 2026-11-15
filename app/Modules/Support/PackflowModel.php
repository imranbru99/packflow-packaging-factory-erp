<?php

namespace App\Modules\Support;

use Illuminate\Database\Eloquent\Model;

abstract class PackflowModel extends Model
{
    protected $guarded = ['id'];
}
