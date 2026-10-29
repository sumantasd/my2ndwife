<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Page;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminPageController extends Controller
{
    public function index(Request $request): View
    {
        $query = Page::query();

        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('slug', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $status = $request->input('status');
            if ($status === 'published') {
                $query->where('is_published', true);
            } elseif ($status === 'draft') {
                $query->where('is_published', false);
            }
        }

        $pages = $query->orderBy('display_order')->orderBy('title')->paginate(15)->withQueryString();

        $stats = [
            'total' => Page::count(),
            'published' => Page::where('is_published', true)->count(),
            'draft' => Page::where('is_published', false)->count(),
        ];

        return view('admin.pages.index', compact('pages', 'stats'));
    }

    public function create(): View
    {
        return view('admin.pages.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'slug' => ['nullable', 'string', 'max:200', 'unique:pages,slug', 'regex:/^[a-z0-9\-]+$/i'],
            'content' => ['required', 'string'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'display_order' => ['nullable', 'integer', 'min:0'],
            'is_published' => ['nullable', 'boolean'],
        ]);

        if (empty($validated['slug'])) {
            $validated['slug'] = Str::slug($validated['title']);
            // Ensure unique slug
            $originalSlug = $validated['slug'];
            $count = 1;
            while (Page::where('slug', $validated['slug'])->exists()) {
                $validated['slug'] = $originalSlug . '-' . $count++;
            }
        } else {
            $validated['slug'] = Str::slug($validated['slug']);
        }

        $validated['is_published'] = $request->boolean('is_published');
        $validated['display_order'] = (int) ($validated['display_order'] ?? 0);

        $page = Page::create($validated);

        AuditLog::record(
            action: 'page_created',
            targetType: 'Page',
            targetId: $page->id,
            details: ['title' => $page->title, 'slug' => $page->slug]
        );

        return redirect()->route('admin.pages.index')->with('success', "Page '{$page->title}' created successfully.");
    }

    public function edit(Page $page): View
    {
        return view('admin.pages.edit', compact('page'));
    }

    public function update(Request $request, Page $page): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:200'],
            'slug' => [
                'required',
                'string',
                'max:200',
                Rule::unique('pages', 'slug')->ignore($page->id),
                'regex:/^[a-z0-9\-]+$/i',
            ],
            'content' => ['required', 'string'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'display_order' => ['nullable', 'integer', 'min:0'],
            'is_published' => ['nullable', 'boolean'],
        ]);

        $validated['slug'] = Str::slug($validated['slug']);
        $validated['is_published'] = $request->boolean('is_published');
        $validated['display_order'] = (int) ($validated['display_order'] ?? 0);

        $page->update($validated);

        AuditLog::record(
            action: 'page_updated',
            targetType: 'Page',
            targetId: $page->id,
            details: ['title' => $page->title, 'slug' => $page->slug]
        );

        return redirect()->route('admin.pages.index')->with('success', "Page '{$page->title}' updated successfully.");
    }

    public function toggle(Page $page): RedirectResponse
    {
        $page->update(['is_published' => !$page->is_published]);

        AuditLog::record(
            action: 'page_status_toggled',
            targetType: 'Page',
            targetId: $page->id,
            details: ['slug' => $page->slug, 'is_published' => $page->is_published]
        );

        $status = $page->is_published ? 'published' : 'unpublished (draft)';
        return back()->with('success', "Page '{$page->title}' is now {$status}.");
    }

    public function destroy(Page $page): RedirectResponse
    {
        // Protected core pages
        $corePages = ['about', 'terms', 'privacy', 'refund-policy', 'faq', 'contact'];
        if (in_array($page->slug, $corePages)) {
            return back()->with('error', "The core page '{$page->title}' is essential for website operations and cannot be deleted. You may unpublish it instead.");
        }

        $title = $page->title;
        $page->delete();

        AuditLog::record(
            action: 'page_deleted',
            targetType: 'Page',
            targetId: $page->id,
            details: ['title' => $title]
        );

        return redirect()->route('admin.pages.index')->with('success', "Page '{$title}' deleted successfully.");
    }
}
