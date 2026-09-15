<?php

namespace Modules\Auth\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    public function index(): View
    {
        $permissions = Permission::withCount('roles')
            ->orderBy('name')
            ->get()
            ->groupBy(function ($p) {
                // Group by the noun part: manage_products → products
                return explode('_', $p->name, 2)[1] ?? $p->name;
            });

        return view('auth::permissions.index', compact('permissions'));
    }

    public function create(): View
    {
        return view('auth::permissions.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:permissions,name', 'regex:/^[a-z][a-z0-9_]*$/'],
        ], [
            'name.regex' => 'Permission name must be lowercase letters, numbers, and underscores only (e.g. manage_products).',
        ]);

        Permission::create(['name' => $request->name, 'guard_name' => 'web']);

        return redirect()->route('admin.permissions.index')
            ->with('success', "Permission \"{$request->name}\" created successfully.");
    }

    public function destroy(Permission $permission): RedirectResponse
    {
        $permission->delete();

        return redirect()->route('admin.permissions.index')
            ->with('success', 'Permission deleted successfully.');
    }
}
