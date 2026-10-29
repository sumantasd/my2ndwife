<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Addon;
use App\Models\AddonGroup;
use App\Models\AuditLog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminAddonController extends Controller
{
    public function index(): View
    {
        $groups = AddonGroup::with(['addons'])->orderBy('display_order')->get();
        return view('admin.addons.index', compact('groups'));
    }

    public function storeGroup(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'min_selection' => ['required', 'integer', 'min:0'],
            'max_selection' => ['nullable', 'integer', 'gte:min_selection'],
            'is_required' => ['nullable', 'boolean'],
            'display_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $group = AddonGroup::create([
            'name' => $validated['name'],
            'min_selection' => $validated['min_selection'],
            'max_selection' => $validated['max_selection'] ?? null,
            'is_required' => $request->boolean('is_required'),
            'display_order' => $validated['display_order'] ?? 0,
            'is_active' => true,
        ]);

        AuditLog::record('addon_group_created', 'AddonGroup', $group->id, ['name' => $group->name]);

        return back()->with('success', "Addon Group '{$group->name}' created successfully.");
    }

    public function storeAddon(Request $request, AddonGroup $group): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'price' => ['required', 'numeric', 'min:0'],
            'display_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $addon = $group->addons()->create([
            'name' => $validated['name'],
            'price' => $validated['price'],
            'display_order' => $validated['display_order'] ?? 0,
            'is_active' => true,
        ]);

        AuditLog::record('addon_created', 'Addon', $addon->id, ['name' => $addon->name, 'group' => $group->name]);

        return back()->with('success', "Addon '{$addon->name}' added to '{$group->name}'.");
    }

    public function destroyAddon(Addon $addon): RedirectResponse
    {
        $name = $addon->name;
        $addon->delete();

        return back()->with('success', "Addon '{$name}' deleted successfully.");
    }

    public function destroyGroup(AddonGroup $group): RedirectResponse
    {
        $name = $group->name;
        $group->delete();

        return back()->with('success', "Addon Group '{$name}' deleted successfully.");
    }
}
