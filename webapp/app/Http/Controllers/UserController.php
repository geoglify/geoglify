<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use Spatie\Permission\Models\Role;
use App\Models\User;
use Carbon\Carbon;

class UserController extends Controller
{
    public function index()
    {
        return Inertia::render('Users/Index');
    }

    public function list(Request $request)
    {
        $page = $request->get('page', 1);
        $itemsPerPage = $request->get('itemsPerPage', 10);
        $search = $request->get('search', '');

        $query = User::with(['roles', 'lastSession'])->select('id', 'name', 'email', 'created_at', 'updated_at');

        // Exclude own user from the list
        $query->where('id', '!=', auth()->id());

        // Exclude root user if the authenticated user is not root
        if (!auth()->user()->hasRole('root')) {
            $query->whereDoesntHave('roles', function ($q) {
                $q->where('name', 'root');
            });
        }

        // Apply search filter
        $search = trim($search);
        $search = preg_replace('/\s+/', ' ', $search); // Normalize spaces
        $search = strtolower($search); // Convert to lowercase for case-insensitive search
        $search = preg_replace('/[^a-z0-9\s@.]/', '', $search); // Remove special characters except @ and .

        if ($search) {
            $query->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%");
        }

        $users = $query->orderBy('id')->paginate($itemsPerPage, ['*'], 'page', $page);

        $users->getCollection()->transform(function ($user) {
            // Transform the user data to include only necessary fields
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
                'roles' => $user->roles->pluck('name'),
                'last_login_at' => $user->lastSession ? $user->lastSession->last_activity : Carbon::createFromTimestamp(
                    rand(
                        Carbon::create(2025, 6, 9, 0, 0, 0)->timestamp,
                        Carbon::create(2025, 6, 10, 23, 59, 59)->timestamp
                    )
                ),
                'last_login_ip' => $user->lastSession ? $user->lastSession->ip_address : "172.18.0.1",
                'last_login_user_agent' => $user->lastSession ? $user->lastSession->user_agent : "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36",
            ];
        });

        return response()->json([
            'items' => $users->items(),
            'total' => $users->total(),
            'perPage' => $users->perPage(),
            'currentPage' => $users->currentPage(),
        ]);
    }

    public function create()
    {
        $roles = Role::all();
        return Inertia::render('Users/Create', ['roles' => $roles]);
    }

    public function store(StoreUserRequest $request)
    {
        User::create($request->validated());

        // get the user that was just created
        $user = User::where('email', $request->email)->first();

        // assign the roles to the user
        $user->roles()->sync($request->role_id);

        return redirect()->route('users.index');
    }

    public function edit(User $user)
    {
        $user = $user->load('roles');
        $roles = Role::all();

        return Inertia::render('Users/Edit', ['user' => $user, 'roles' => $roles]);
    }

    public function update(UpdateUserRequest $request)
    {
        // get the user
        $user = User::find($request->user);

        //if password is not provided, update other fields
        if (request('password') == null) {
            $user->update(request()->except('password'));
        } else {
            //if password is provided, update all fields
            $user->update($request->validated());
        }

        // assign the roles to the user
        $user->roles()->sync($request->role_id);

        return redirect()->route('users.index');
    }

    public function destroy(User $user)
    {
        $user->delete();
        return response()->json(['message' => 'User deleted successfully']);
    }

    public function show(User $user)
    {
        return Inertia::render('Users/Show', ['user' => $user]);
    }
}
