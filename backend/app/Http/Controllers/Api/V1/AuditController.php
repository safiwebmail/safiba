<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditController extends Controller
{
    public function index(Request $request)
    {
        if (!$request->user()->isAdmin()) {
            return $this->error('Unauthorized', 403);
        }

        $logs = AuditLog::with('user:id,name')
            ->when($request->query('action'), fn ($q, $a) => $q->where('action', 'ilike', "%{$a}%"))
            ->orderBy('created_at', 'desc')
            ->paginate($request->query('per_page', 25));

        return $this->success($logs->through(fn ($l) => [
            'id' => $l->id,
            'action' => $l->action,
            'entity_type' => $l->entity_type,
            'entity_id' => $l->entity_id,
            'description' => $l->description,
            'user' => $l->user?->name,
            'ip_address' => $l->ip_address,
            'created_at' => $l->created_at?->toISOString(),
        ]), 'Success');
    }
}
