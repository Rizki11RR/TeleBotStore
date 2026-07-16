<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    /** @var list<string> */
    protected $fillable = [
        'admin_id',
        'action',
        'description',
        'subject_type',
        'subject_id',
        'ip_address',
        'user_agent',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }

    public function subject()
    {
        return $this->morphTo();
    }
}
