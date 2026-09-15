<?php

namespace Modules\Auth\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Auth\Models\NavigationItem;
use Spatie\Permission\Models\Permission;

class NavigationItemController extends Controller
{
    public function index(): View
    {
        $items = NavigationItem::with('children')
            ->whereNull('parent_id')
            ->orderBy('sort_order')
            ->get();

        return view('auth::navigation.index', compact('items'));
    }

    public function create(): View
    {
        $parents = NavigationItem::whereNull('parent_id')
            ->orderBy('sort_order')
            ->pluck('label', 'id');

        $permissions = Permission::orderBy('name')->pluck('name', 'name');

        return view('auth::navigation.create', compact('parents', 'permissions'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'label' => ['required', 'string', 'max:100'],
            'route_name' => ['nullable', 'string', 'max:150'],
            'icon' => ['nullable', 'string', 'max:100'],
            'permission_required' => ['nullable', 'string', 'max:100'],
            'parent_id' => ['nullable', 'exists:navigation_items,id'],
            'module' => ['nullable', 'string', 'max:50'],
            'sort_order' => ['integer', 'min:0'],
            'is_active' => ['boolean'],
        ]);

        NavigationItem::create([
            'label' => $request->label,
            'route_name' => $request->route_name ?: null,
            'icon' => $request->icon ?: null,
            'permission_required' => $request->permission_required ?: null,
            'parent_id' => $request->parent_id ?: null,
            'module' => $request->module ?: null,
            'sort_order' => $request->input('sort_order', 0),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.navigation.index')
            ->with('success', 'Menu item created successfully.');
    }

    public function edit(NavigationItem $navigation): View
    {
        $parents = NavigationItem::whereNull('parent_id')
            ->where('id', '!=', $navigation->id)
            ->orderBy('sort_order')
            ->pluck('label', 'id');

        $permissions = Permission::orderBy('name')->pluck('name', 'name');

        return view('auth::navigation.edit', compact('navigation', 'parents', 'permissions'));
    }

    public function update(Request $request, NavigationItem $navigation): RedirectResponse
    {
        $request->validate([
            'label' => ['required', 'string', 'max:100'],
            'route_name' => ['nullable', 'string', 'max:150'],
            'icon' => ['nullable', 'string', 'max:100'],
            'permission_required' => ['nullable', 'string', 'max:100'],
            'parent_id' => ['nullable', 'exists:navigation_items,id'],
            'module' => ['nullable', 'string', 'max:50'],
            'sort_order' => ['integer', 'min:0'],
            'is_active' => ['boolean'],
        ]);

        $navigation->update([
            'label' => $request->label,
            'route_name' => $request->route_name ?: null,
            'icon' => $request->icon ?: null,
            'permission_required' => $request->permission_required ?: null,
            'parent_id' => $request->parent_id ?: null,
            'module' => $request->module ?: null,
            'sort_order' => $request->input('sort_order', 0),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.navigation.index')
            ->with('success', 'Menu item updated successfully.');
    }

    public function destroy(NavigationItem $navigation): RedirectResponse
    {
        // Re-parent children to null (become top-level) before deleting
        $navigation->children()->update(['parent_id' => null]);
        $navigation->delete();

        return redirect()->route('admin.navigation.index')
            ->with('success', 'Menu item deleted. Its sub-items were moved to top level.');
    }
}
