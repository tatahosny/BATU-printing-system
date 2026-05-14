<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): string|null
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     */
    public function share(Request $request): array
    {
        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $request->user() ? [
                    'id'   => $request->user()->id,
                    'name' => $request->user()->name,
                    'email'=> $request->user()->email,
                    'role' => $request->user()->role,
                    'assignments' => $request->user()->assignments()->with(['university', 'college', 'department', 'batch', 'section'])->get(),
                ] : null,
                'permissions' => [
                    'isAdmin' => $request->user() ? $request->user()->isAdmin() : false,
                    'isGeneralDelegate' => $request->user() ? $request->user()->isGeneralDelegate() : false,
                    'isSectionDelegate' => $request->user() ? $request->user()->isSectionDelegate() : false,
                ]
            ],
            // لمشاركة رسائل النجاح أو الفشل
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error'   => fn () => $request->session()->get('error'),
            ],
        ]);
    }
}
