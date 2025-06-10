<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use Tighten\Ziggy\Ziggy;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
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
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();
        $can = [];

        if ($user) {

            $user->load(['roles', 'permissions']);

            // Use the user's permissions
            $permissions = $user->getAllPermissions()->map(function ($permission) {
                return ['name' => $permission->name];
            });

            $can = $permissions->map(function ($permission) use ($user) {
                return [$permission['name'] => $user->can($permission['name'])];
            })->collapse()->all();
            
        }

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user,
                'role' => $user?->roles->pluck('name')->first(),
                'can' => $can,
            ],
            'csrf_token' => $request->session()->token(),
            'locale' => app()->getLocale(),
            'ziggy' => fn() => [
                ...(new Ziggy)->toArray(),
                'location' => $request->url(),
            ],
        ];
    }
}
