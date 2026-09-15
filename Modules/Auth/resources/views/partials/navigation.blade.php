@php
    use Modules\Auth\Models\NavigationItem;

    // Cache the nav tree for this request so it isn't queried on every include
    $navTree = once(function () {
        return NavigationItem::active()
            ->whereNull('parent_id')
            ->with(['children' => fn($q) => $q->active()])
            ->orderBy('sort_order')
            ->get();
    });

    $currentRoute = request()->route()?->getName() ?? '';
@endphp

{{-- Dashboard (always visible to authenticated users) --}}
<li class="nav-item">
    <a href="{{ route('admin.dashboard') }}"
       class="nav-link {{ $currentRoute === 'admin.dashboard' ? 'active' : '' }}">
        <i class="nav-icon fas fa-tachometer-alt"></i>
        <p>Dashboard</p>
    </a>
</li>

@foreach($navTree as $item)
    @if($item->permission_required && ! auth()->user()->can($item->permission_required))
        @continue
    @endif

    @if($item->children->isNotEmpty())
        {{-- Has children: render as treeview menu --}}
        @php
            $hasActiveChild = $item->children->contains(fn($child) =>
                $child->route_name && str_starts_with($currentRoute, rtrim($child->route_name, '.*'))
            );
        @endphp
        <li class="nav-item has-treeview {{ $hasActiveChild ? 'menu-open' : '' }}">
            <a href="#" class="nav-link {{ $hasActiveChild ? 'active' : '' }}">
                <i class="nav-icon {{ $item->icon ?? 'fas fa-circle' }}"></i>
                <p>
                    {{ $item->label }}
                    <i class="right fas fa-angle-left"></i>
                </p>
            </a>
            <ul class="nav nav-treeview">
                @foreach($item->children as $child)
                    @if($child->permission_required && ! auth()->user()->can($child->permission_required))
                        @continue
                    @endif
                    @php
                        $childActive = $child->route_name && str_starts_with($currentRoute, rtrim($child->route_name, '.*'));
                    @endphp
                    <li class="nav-item">
                        <a href="{{ ($child->route_name && \Illuminate\Support\Facades\Route::has($child->route_name)) ? route($child->route_name) : '#' }}"
                           class="nav-link {{ $childActive ? 'active' : '' }}">
                            <i class="{{ $child->icon ?? 'far fa-circle' }} nav-icon"></i>
                            <p>{{ $child->label }}</p>
                        </a>
                    </li>
                @endforeach
            </ul>
        </li>
    @else
        {{-- No children: flat item --}}
        @php
            $itemActive = $item->route_name && str_starts_with($currentRoute, rtrim($item->route_name, '.*'));
        @endphp
        <li class="nav-item">
            <a href="{{ ($item->route_name && \Illuminate\Support\Facades\Route::has($item->route_name)) ? route($item->route_name) : '#' }}"
               class="nav-link {{ $itemActive ? 'active' : '' }}">
                <i class="nav-icon {{ $item->icon ?? 'fas fa-circle' }}"></i>
                <p>{{ $item->label }}</p>
            </a>
        </li>
    @endif
@endforeach

{{-- Administration section (always shown to users with any admin permission) --}}
@canany(['manage_users', 'manage_settings'])
<li class="nav-header text-muted" style="font-size:.7rem; letter-spacing:.08em;">ADMINISTRATION</li>
@endcanany

@can('manage_users')
<li class="nav-item">
    <a href="{{ route('admin.users.index') }}"
       class="nav-link {{ str_starts_with($currentRoute, 'admin.users') ? 'active' : '' }}">
        <i class="nav-icon fas fa-users"></i>
        <p>Users</p>
    </a>
</li>

<li class="nav-item {{ in_array(true, [str_starts_with($currentRoute, 'admin.roles'), str_starts_with($currentRoute, 'admin.permissions')]) ? 'menu-open' : '' }}">
    <a href="#" class="nav-link {{ in_array(true, [str_starts_with($currentRoute, 'admin.roles'), str_starts_with($currentRoute, 'admin.permissions')]) ? 'active' : '' }}">
        <i class="nav-icon fas fa-user-shield"></i>
        <p>Roles & Permissions <i class="right fas fa-angle-left"></i></p>
    </a>
    <ul class="nav nav-treeview">
        <li class="nav-item">
            <a href="{{ route('admin.roles.index') }}"
               class="nav-link {{ str_starts_with($currentRoute, 'admin.roles') ? 'active' : '' }}">
                <i class="far fa-circle nav-icon"></i>
                <p>Roles</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('admin.permissions.index') }}"
               class="nav-link {{ str_starts_with($currentRoute, 'admin.permissions') ? 'active' : '' }}">
                <i class="far fa-circle nav-icon"></i>
                <p>Permissions</p>
            </a>
        </li>
    </ul>
</li>
@endcan

@can('manage_settings')
<li class="nav-item has-treeview {{ in_array($currentRoute, ['admin.settings.index', 'admin.lookups.index', 'admin.lookups.create', 'admin.lookups.edit']) || str_starts_with($currentRoute, 'admin.navigation') ? 'menu-open' : '' }}">
    <a href="#" class="nav-link {{ in_array($currentRoute, ['admin.settings.index', 'admin.lookups.index', 'admin.lookups.create', 'admin.lookups.edit']) || str_starts_with($currentRoute, 'admin.navigation') ? 'active' : '' }}">
        <i class="nav-icon fas fa-cog"></i>
        <p>
            Settings
            <i class="right fas fa-angle-left"></i>
        </p>
    </a>
    <ul class="nav nav-treeview">
        <li class="nav-item">
            <a href="{{ route('admin.settings.index') }}"
               class="nav-link {{ $currentRoute === 'admin.settings.index' ? 'active' : '' }}">
                <i class="far fa-circle nav-icon"></i>
                <p>General Settings</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('admin.lookups.index') }}"
               class="nav-link {{ str_starts_with($currentRoute, 'admin.lookups') ? 'active' : '' }}">
                <i class="far fa-circle nav-icon"></i>
                <p>Lookup Values</p>
            </a>
        </li>
        <li class="nav-item">
            <a href="{{ route('admin.navigation.index') }}"
               class="nav-link {{ str_starts_with($currentRoute, 'admin.navigation') ? 'active' : '' }}">
                <i class="far fa-circle nav-icon"></i>
                <p>Navigation Menu</p>
            </a>
        </li>
    </ul>
</li>
@endcan
