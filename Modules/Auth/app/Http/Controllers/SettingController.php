<?php

namespace Modules\Auth\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Modules\Auth\Http\Requests\StoreSettingRequest;
use Modules\Auth\Models\Setting;

class SettingController extends Controller
{
    public function index(): View
    {
        $settings = Setting::orderBy('group')->orderBy('key')->get()->groupBy('group');

        return view('auth::settings.index', compact('settings'));
    }

    public function update(StoreSettingRequest $request): RedirectResponse
    {
        foreach ($request->input('settings', []) as $item) {
            Setting::setValue($item['key'], $item['value'] ?? null);
        }

        return back()->with('success', 'Settings saved successfully.');
    }
}
