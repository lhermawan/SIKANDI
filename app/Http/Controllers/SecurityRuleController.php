<?php

namespace App\Http\Controllers;

use App\Models\SecurityRule;
use Illuminate\Http\Request;

class SecurityRuleController extends Controller
{
    public function index()
    {
        $rules = SecurityRule::all();
        return view('security.rules.index', compact('rules'));
    }

    public function toggle(SecurityRule $rule)
    {
        $rule->update(['enabled' => !$rule->enabled]);
        return back()->with('success', 'Status rule ' . $rule->name . ' berhasil diubah menjadi ' . ($rule->enabled ? 'Aktif' : 'Non-aktif') . '.');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|unique:security_rules',
            'threshold' => 'required|integer|min:1',
            'time_window_seconds' => 'required|integer|min:1',
            'severity' => 'required|in:critical,high,medium,low,info',
            'risk_score' => 'required|integer|min:0|max:100',
            'auto_incident' => 'boolean'
        ]);
        
        $validated['name'] = strtoupper(str_replace(' ', '_', $validated['name']));
        $validated['enabled'] = true;

        SecurityRule::create($validated);
        return back()->with('success', 'Security Rule berhasil ditambahkan.');
    }

    public function update(Request $request, SecurityRule $rule)
    {
        $validated = $request->validate([
            'threshold' => 'required|integer|min:1',
            'time_window_seconds' => 'required|integer|min:1',
            'severity' => 'required|in:critical,high,medium,low,info',
            'risk_score' => 'required|integer|min:0|max:100',
            'auto_incident' => 'boolean'
        ]);

        $rule->update($validated);
        return back()->with('success', 'Security Rule berhasil diperbarui.');
    }
}
