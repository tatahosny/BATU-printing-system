<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\UserActivityLog;
use Illuminate\Http\Request;

class LogUserActivity
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        if (auth()->check()) {
            $action = $this->determineAction($request);
            
            // Only log significant actions or page views
            if ($action) {
                UserActivityLog::create([
                    'user_id' => auth()->id(),
                    'action' => $action,
                    'url' => $request->path(),
                    'payload' => $this->filterPayload($request->all()),
                    'ip_address' => $request->ip(),
                ]);
            }
        }

        return $response;
    }

    private function determineAction(Request $request)
    {
        if ($request->isMethod('GET')) {
            if ($request->has('search')) return 'search';
            return 'page_view';
        }

        if ($request->isMethod('POST') || $request->isMethod('PUT') || $request->isMethod('PATCH') || $request->isMethod('DELETE')) {
            if (str_contains($request->path(), 'upload')) return 'file_upload';
            return 'action_execution';
        }

        return null;
    }

    private function filterPayload(array $payload)
    {
        // Remove sensitive fields
        unset($payload['password'], $payload['password_confirmation'], $payload['_token'], $payload['excel_file'], $payload['sheet']);
        
        // Truncate large payloads
        return array_slice($payload, 0, 10);
    }
}
