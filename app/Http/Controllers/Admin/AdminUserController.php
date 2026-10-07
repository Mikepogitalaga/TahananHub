<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\ActivityLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AdminUserController extends Controller
{
    public function __construct(private ActivityLogService $activityLog) {}

    public function index(Request $request): View
    {
        $query = User::query()->latest();

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $users = $query->paginate(15)->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    public function create(): View
    {
        return view('admin.users.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'unique:users,email'],
            'phone'    => ['nullable', 'string', 'max:20'],
            'role'     => ['required', 'in:super_admin,owner,tenant'],
            'password' => ['required', 'string', 'min:8'],
            'status'   => ['required', 'in:active,suspended,inactive'],
        ]);

        $user = User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'phone'    => $validated['phone'] ?? null,
            'role'     => $validated['role'],
            'status'   => $validated['status'],
            'password' => Hash::make($validated['password']),
        ]);

        $this->activityLog->log('create_user', 'users', $user->id, $user->name);

        return redirect()->route('admin.users.index')->with('success', "User account {$user->name} created successfully.");
    }

    public function edit(User $user): View
    {
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'email', 'unique:users,email,' . $user->id],
            'phone'    => ['nullable', 'string', 'max:20'],
            'role'     => ['required', 'in:super_admin,owner,tenant'],
            'status'   => ['required', 'in:active,suspended,inactive'],
            'password' => ['nullable', 'string', 'min:8'],
        ]);

        $user->name   = $validated['name'];
        $user->email  = $validated['email'];
        $user->phone  = $validated['phone'] ?? null;
        $user->role   = $validated['role'];
        $user->status = $validated['status'];

        if (! empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        $this->activityLog->log('update_user', 'users', $user->id, $user->name);

        return redirect()->route('admin.users.index')->with('success', "User account {$user->name} updated successfully.");
    }

    public function toggleStatus(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot change the status of your own account.');
        }

        $newStatus = $user->status === 'active' ? 'suspended' : 'active';
        $user->update(['status' => $newStatus]);

        $this->activityLog->log('toggle_status', 'users', $user->id, "{$user->name} set to {$newStatus}");

        return back()->with('success', "User {$user->name} is now {$newStatus}.");
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $name = $user->name;
        $user->delete();

        $this->activityLog->log('delete_user', 'users', null, $name);

        return redirect()->route('admin.users.index')->with('success', "User {$name} deleted.");
    }
}

