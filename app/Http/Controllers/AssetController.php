<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetCategory;
use App\Models\Location;
use App\Models\Organization;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class AssetController extends Controller
{
    public function index(Request $request): View
    {
        $query = Asset::with(['category', 'organization', 'location', 'assignedTo', 'configurationItems']);

        if ($request->filled('category')) {
            $query->where('asset_category_id', $request->category);
        }
        if ($request->filled('status')) {
            $query->where('lifecycle_status', $request->status);
        }
        if ($request->filled('condition')) {
            $query->where('condition', $request->condition);
        }
        if ($request->filled('org')) {
            $query->where('organization_id', $request->org);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('asset_number', 'like', "%{$search}%")
                    ->orWhere('serial_number', 'like', "%{$search}%")
                    ->orWhere('brand', 'like', "%{$search}%");
            });
        }

        $assets = $query->latest()->paginate(15)->withQueryString();
        $categories = AssetCategory::orderBy('name')->get();
        $organizations = Organization::orderBy('name')->get();

        return view('itam.index', compact('assets', 'categories', 'organizations'));
    }

    public function create(): View
    {
        $categories = AssetCategory::orderBy('name')->get();
        $organizations = Organization::orderBy('name')->get();
        $locations = Location::orderBy('name')->get();
        $vendors = Vendor::orderBy('name')->get();
        $users = User::where('is_active', true)->orderBy('name')->get();

        return view('itam.create', compact('categories', 'organizations', 'locations', 'vendors', 'users'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'asset_category_id' => 'required|exists:asset_categories,id',
            'vendor_id' => 'nullable|exists:vendors,id',
            'organization_id' => 'required|exists:organizations,id',
            'location_id' => 'nullable|exists:locations,id',
            'assigned_to_user_id' => 'nullable|exists:users,id',
            'brand' => 'nullable|string|max:255',
            'model' => 'nullable|string|max:255',
            'serial_number' => 'nullable|string|max:255',
            'purchase_date' => 'nullable|date',
            'purchase_price' => 'nullable|numeric',
            'warranty_expiry_date' => 'nullable|date',
            'lifecycle_status' => 'required|in:procurement,received,inventory,assigned,in_use,maintenance,repair,returned,disposed',
            'condition' => 'required|in:good,light_damage,heavy_damage,lost',
            'notes' => 'nullable|string',
        ]);

        $asset = Asset::create($validated);

        return redirect()->route('itam.show', $asset)->with('success', "Aset {$asset->asset_number} berhasil didaftarkan.");
    }

    public function show(Asset $asset): View
    {
        $asset->load([
            'category',
            'vendor',
            'organization',
            'location',
            'assignedTo',
            'configurationItems.ciType',
            'assignments.user',
            'maintenances',
            'tickets',
            'incidents',
        ]);

        // Generate QR Code SVG
        $scanUrl = route('itam.scan', $asset->qr_code_token);
        $qrCodeSvg = QrCode::size(140)->color(255, 255, 255)->backgroundColor(15, 23, 42)->generate($scanUrl);

        return view('itam.show', compact('asset', 'qrCodeSvg', 'scanUrl'));
    }

    public function edit(Asset $asset): View
    {
        $categories = AssetCategory::orderBy('name')->get();
        $organizations = Organization::orderBy('name')->get();
        $locations = Location::orderBy('name')->get();
        $vendors = Vendor::orderBy('name')->get();
        $users = User::where('is_active', true)->orderBy('name')->get();

        return view('itam.edit', compact('asset', 'categories', 'organizations', 'locations', 'vendors', 'users'));
    }

    public function update(Request $request, Asset $asset): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'asset_category_id' => 'required|exists:asset_categories,id',
            'vendor_id' => 'nullable|exists:vendors,id',
            'organization_id' => 'required|exists:organizations,id',
            'location_id' => 'nullable|exists:locations,id',
            'assigned_to_user_id' => 'nullable|exists:users,id',
            'brand' => 'nullable|string|max:255',
            'model' => 'nullable|string|max:255',
            'serial_number' => 'nullable|string|max:255',
            'purchase_date' => 'nullable|date',
            'purchase_price' => 'nullable|numeric',
            'warranty_expiry_date' => 'nullable|date',
            'lifecycle_status' => 'required|in:procurement,received,inventory,assigned,in_use,maintenance,repair,returned,disposed',
            'condition' => 'required|in:good,light_damage,heavy_damage,lost',
            'notes' => 'nullable|string',
        ]);

        $asset->update($validated);

        return redirect()->route('itam.show', $asset)->with('success', "Data Aset {$asset->asset_number} berhasil diperbarui.");
    }

    public function printLabel(Asset $asset): View
    {
        $scanUrl = route('itam.scan', $asset->qr_code_token);
        $qrCodeSvg = QrCode::size(160)->generate($scanUrl);

        return view('itam.print-label', compact('asset', 'qrCodeSvg'));
    }

    /**
     * Mobile / Public scan landing page for QR code
     */
    public function scan(string $token): View
    {
        $asset = Asset::where('qr_code_token', $token)
            ->with(['category', 'organization', 'location', 'assignedTo', 'configurationItems', 'maintenances'])
            ->firstOrFail();

        return view('itam.scan', compact('asset'));
    }
}
