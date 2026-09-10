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
        return back()->with('success', 'Status rule berhasil diubah.');
    }
}
