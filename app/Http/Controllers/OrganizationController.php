<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrganizationController extends Controller
{
    public function index(Request $request): View
    {
        $query = Organization::withCount(['users', 'assets', 'configurationItems']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('head_name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        $organizations = $query->orderBy('name')->paginate(20)->withQueryString();

        return view('admin.organizations.index', compact('organizations'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|unique:organizations,code',
            'name' => 'required|string|max:255',
            'category' => 'required|string',
            'address' => 'nullable|string',
            'phone' => 'nullable|string',
            'email' => 'nullable|email',
            'website_url' => 'nullable|url',
            'head_name' => 'nullable|string',
            'head_nip' => 'nullable|string',
        ]);

        $validated['is_active'] = $request->has('is_active');

        Organization::create($validated);

        return back()->with('success', 'OPD / Unit berhasil ditambahkan.');
    }

    public function update(Request $request, Organization $organization): RedirectResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|unique:organizations,code,'.$organization->id,
            'name' => 'required|string|max:255',
            'category' => 'required|string',
            'address' => 'nullable|string',
            'phone' => 'nullable|string',
            'email' => 'nullable|email',
            'website_url' => 'nullable|url',
            'head_name' => 'nullable|string',
            'head_nip' => 'nullable|string',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $organization->update($validated);

        return back()->with('success', 'Data OPD / Unit berhasil diperbarui.');
    }

    public function destroy(Organization $organization): RedirectResponse
    {
        // Don't allow deletion if there are users tied to it
        if ($organization->users()->count() > 0) {
            return back()->with('error', 'Tidak dapat menghapus OPD karena masih memiliki User terkait.');
        }

        $organization->delete();

        return back()->with('success', 'OPD / Unit berhasil dihapus.');
    }
}
