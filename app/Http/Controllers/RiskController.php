<?php

namespace App\Http\Controllers;

use App\Models\ConfigurationItem;
use App\Models\Organization;
use App\Models\Risk;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RiskController extends Controller
{
    public function index(Request $request): View
    {
        $query = Risk::with(['organization', 'configurationItem', 'owner', 'treatments']);

        if ($request->filled('level')) {
            $query->where('risk_level', $request->level);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $risks = $query->latest()->paginate(15)->withQueryString();

        // 5x5 Heatmap Matrix distribution
        $matrix = [];
        for ($i = 1; $i <= 5; $i++) {
            for ($l = 1; $l <= 5; $l++) {
                $matrix[$l][$i] = Risk::where('likelihood', $l)->where('impact', $i)->count();
            }
        }

        $stats = [
            'critical' => Risk::where('risk_level', 'critical')->count(),
            'high' => Risk::where('risk_level', 'high')->count(),
            'medium' => Risk::where('risk_level', 'medium')->count(),
            'low' => Risk::where('risk_level', 'low')->count(),
        ];

        return view('security.risks', compact('risks', 'matrix', 'stats'));
    }

    public function create(): View
    {
        $organizations = Organization::orderBy('name')->get();
        $cis = ConfigurationItem::orderBy('name')->get();
        $users = User::where('is_active', true)->orderBy('name')->get();

        return view('security.create-risk', compact('organizations', 'cis', 'users'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'organization_id' => 'required|exists:organizations,id',
            'ci_id' => 'nullable|exists:configuration_items,id',
            'threat' => 'nullable|string',
            'vulnerability' => 'nullable|string',
            'likelihood' => 'required|integer|min:1|max:5',
            'impact' => 'required|integer|min:1|max:5',
            'owner_id' => 'nullable|exists:users,id',
            'due_date' => 'nullable|date',
        ]);

        $risk = Risk::create($validated);

        return redirect()->route('security.risks')->with('success', "Risiko {$risk->risk_code} berhasil didaftarkan ke Risk Register.");
    }

    public function storeTreatment(Request $request, Risk $risk): RedirectResponse
    {
        $validated = $request->validate([
            'strategy' => 'required|in:mitigate,accept,transfer,avoid',
            'action_plan' => 'required|string',
            'assigned_to' => 'nullable|exists:users,id',
            'progress_percent' => 'nullable|integer|min:0|max:100',
        ]);

        $treatment = $risk->treatments()->create($validated);

        return back()->with('success', 'Rencana mitigasi / perlakuan risiko berhasil disimpan.');
    }
}
