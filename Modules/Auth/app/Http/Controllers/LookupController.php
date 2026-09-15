<?php

namespace Modules\Auth\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Modules\Auth\Http\Requests\StoreLookupRequest;
use Modules\Auth\Models\Lookup;

class LookupController extends Controller
{
    public function index(Request $request): View
    {
        $type = $request->query('type');
        $query = Lookup::orderBy('type')->orderBy('sort_order')->orderBy('label');

        if ($type) {
            $query->where('type', $type);
        }

        $lookups = $query->paginate(30)->withQueryString();
        $types = Lookup::distinct()->orderBy('type')->pluck('type');

        return view('auth::lookups.index', compact('lookups', 'types', 'type'));
    }

    public function create(): View
    {
        $types = Lookup::distinct()->orderBy('type')->pluck('type');

        return view('auth::lookups.create', compact('types'));
    }

    public function store(StoreLookupRequest $request): RedirectResponse
    {
        Lookup::create($request->validated());

        return redirect()->route('admin.lookups.index')
            ->with('success', 'Lookup value created successfully.');
    }

    public function edit(Lookup $lookup): View
    {
        $types = Lookup::distinct()->orderBy('type')->pluck('type');

        return view('auth::lookups.edit', compact('lookup', 'types'));
    }

    public function update(StoreLookupRequest $request, Lookup $lookup): RedirectResponse
    {
        $lookup->update($request->validated());

        return redirect()->route('admin.lookups.index')
            ->with('success', 'Lookup value updated successfully.');
    }

    public function destroy(Lookup $lookup): RedirectResponse
    {
        $lookup->update(['is_active' => false]);

        return redirect()->route('admin.lookups.index')
            ->with('success', 'Lookup value deactivated successfully.');
    }
}
