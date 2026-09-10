<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Organization;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrganizationController extends Controller
{
    public function index(Request $request): View
    {
        $query = Organization::withCount(['users', 'assets', 'configurationItems']);

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                    ->orWhere('code', 'like', "%{$s}%")
                    ->orWhere('head_name', 'like', "%{$s}%");
            });
        }

        $organizations = $query->orderBy('name')->paginate(15)->withQueryString();

        return view('admin.organizations.index', compact('organizations'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => 'required|string|unique:organizations,code',
            'name' => 'required|string|max:255',
            'category' => 'required|in:dinas,badan,kecamatan,bagian_setda,rsud,lainnya',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email',
            'website_url' => 'nullable|url',
            'head_name' => 'nullable|string|max:255',
            'head_nip' => 'nullable|string|max:50',
        ]);

        $org = Organization::create($validated);

        return back()->with('success', "OPD {$org->name} berhasil ditambahkan.");
    }
}
