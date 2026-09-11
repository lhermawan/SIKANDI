<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Location;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    public function index(Request $request)
    {
        $query = Location::withCount(['assets', 'configurationItems', 'children'])->with('parent');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('building', 'like', "%{$search}%");
            });
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Default: sort by type (locations first), then name
        $locations = $query->orderBy('type')->orderBy('name')->paginate(15)->withQueryString();

        $allLocations = Location::locations()->active()->orderBy('name')->get();

        return view('admin.locations.index', compact('locations', 'allLocations'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:location,room',
            'code' => 'nullable|string|unique:locations,code',
            'parent_id' => 'nullable|exists:locations,id',
            'organization_id' => 'nullable|exists:organizations,id',
            'building' => 'nullable|string',
            'floor' => 'nullable|string',
            'description' => 'nullable|string',
        ]);

        $data = $request->all();
        $data['is_active'] = $request->boolean('is_active', true);
        
        if ($data['type'] === 'location') {
            $data['parent_id'] = null; // Ensure locations don't have parents from UI for now
        }

        Location::create($data);

        return back()->with('success', 'Data lokasi/ruang berhasil ditambahkan.');
    }

    public function update(Request $request, Location $location)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:location,room',
            'code' => 'nullable|string|unique:locations,code,' . $location->id,
            'parent_id' => 'nullable|exists:locations,id',
            'building' => 'nullable|string',
            'floor' => 'nullable|string',
            'description' => 'nullable|string',
        ]);

        $data = $request->all();
        $data['is_active'] = $request->boolean('is_active', true);
        
        if ($data['type'] === 'location') {
            $data['parent_id'] = null;
        }

        $location->update($data);

        return back()->with('success', 'Data lokasi/ruang berhasil diperbarui.');
    }

    public function toggle(Location $location)
    {
        $location->update(['is_active' => !$location->is_active]);
        
        // Optionally deactivate children if parent is deactivated
        if (!$location->is_active && $location->type === 'location') {
            $location->children()->update(['is_active' => false]);
        }

        return back()->with('success', 'Status berhasil diubah.');
    }

    public function destroy(Location $location)
    {
        if ($location->assets()->count() > 0 || $location->configurationItems()->count() > 0) {
            return back()->with('error', 'Tidak dapat menghapus data ini karena sedang digunakan oleh aset atau item CMDB.');
        }

        if ($location->children()->count() > 0) {
            return back()->with('error', 'Tidak dapat menghapus lokasi ini karena masih memiliki sub-ruang.');
        }

        $location->delete();

        return back()->with('success', 'Data berhasil dihapus.');
    }
}
