<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogService
{
    /**
     * Catat aktivitas admin.
     *
     * @param string      $action      Kode aksi (e.g. 'order.verified')
     * @param string      $description Deskripsi lengkap
     * @param object|null $subject     Model yang terkait (opsional)
     */
    public function log(
        string $action,
        string $description,
        ?object $subject = null
    ): ActivityLog {
        $request = app(Request::class);

        return ActivityLog::create([
            'admin_id'     => auth('admin')->id(),
            'action'       => $action,
            'description'  => $description,
            'subject_type' => $subject ? get_class($subject) : null,
            'subject_id'   => $subject?->id,
            'ip_address'   => $request->ip(),
            'user_agent'   => $request->userAgent(),
        ]);
    }
}
