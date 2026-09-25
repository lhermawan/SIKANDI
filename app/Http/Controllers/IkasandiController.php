<?php

namespace App\Http\Controllers;

use App\Models\Assessment;
use App\Models\AssessmentAnswer;
use App\Models\AssessmentCategory;
use App\Models\Organization;
use App\Models\Risk;
use App\Models\SecurityIncident;
use App\Models\Website;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class IkasandiController extends Controller
{
    public function dashboard(): View
    {
        $totalOpd = Organization::count();
        $assessedCount = Organization::whereHas('assessments', function ($q) {
            $q->whereIn('status', ['submitted', 'verified', 'published']);
        })->count();

        $avgScore = round(Assessment::avg('final_score') ?? 0, 1);
        $avgRisk = round(Assessment::avg('risk_score') ?? 0, 1);

        $riskStats = [
            'critical' => Risk::where('risk_level', 'critical')->count(),
            'high' => Risk::where('risk_level', 'high')->count(),
            'medium' => Risk::where('risk_level', 'medium')->count(),
            'low' => Risk::where('risk_level', 'low')->count(),
        ];

        $openIncidents = SecurityIncident::where('workflow_status', '!=', 'closed')->count();
        $websiteIssues = Website::where('current_status', '!=', 'up')->count();

        $assessments = Assessment::with('organization')
            ->latest('updated_at')
            ->paginate(15);

        $topOpd = Assessment::with('organization')
            ->whereIn('status', ['verified', 'published'])
            ->orderByDesc('final_score')
            ->limit(5)
            ->get();

        $bottomOpd = Assessment::with('organization')
            ->whereIn('status', ['verified', 'published'])
            ->orderBy('final_score')
            ->limit(5)
            ->get();

        return view('ikasandi.dashboard', compact(
            'totalOpd',
            'assessedCount',
            'avgScore',
            'avgRisk',
            'riskStats',
            'openIncidents',
            'websiteIssues',
            'assessments',
            'topOpd',
            'bottomOpd'
        ));
    }

    public function assessment(Request $request): View
    {
        $user = Auth::user();
        $orgId = $user->organization_id;

        if ($user->hasAnyRole(['Super Admin', 'Admin Persandian']) && $request->filled('org_id')) {
            $orgId = $request->org_id;
        }

        $organization = Organization::find($orgId) ?? Organization::first();

        // Find or create assessment for this organization for current year
        $assessment = Assessment::firstOrCreate(
            [
                'organization_id' => $organization->id,
                'year' => date('Y'),
            ],
            [
                'title' => 'Penilaian Mandiri Indikator Keamanan Informasi (IKASANDI) '.date('Y'),
                'period' => 'Tahunan',
                'status' => 'draft',
            ]
        );

        $categories = AssessmentCategory::with(['questions' => function ($q) {
            $q->where('is_active', true)->orderBy('order_num');
        }])->orderBy('order_num')->get();

        $existingAnswers = $assessment->answers()->pluck('answer', 'question_id')->toArray();
        $existingNotes = $assessment->answers()->pluck('notes', 'question_id')->toArray();

        $organizations = Organization::orderBy('name')->get();

        return view('ikasandi.assessment', compact('assessment', 'organization', 'categories', 'existingAnswers', 'existingNotes', 'organizations'));
    }

    public function submitAssessment(Request $request, Assessment $assessment): RedirectResponse
    {
        $user = Auth::user();
        if ($user->hasRole('OPD User') && $assessment->organization_id !== $user->organization_id) {
            abort(403, 'Anda tidak berhak mengisi atau mengubah assessment OPD lain.');
        }

        $answers = $request->input('answers', []);
        $notes = $request->input('notes', []);

        foreach ($answers as $questionId => $val) {
            AssessmentAnswer::updateOrCreate(
                [
                    'assessment_id' => $assessment->id,
                    'question_id' => $questionId,
                ],
                [
                    'answer' => $val,
                    'notes' => $notes[$questionId] ?? null,
                    'score' => match ($val) {
                        'compliant' => 100,
                        'partial' => 50,
                        default => 0,
                    },
                ]
            );
        }

        $assessment->recalculateScores();

        if ($request->has('submit_final')) {
            $assessment->update([
                'status' => 'submitted',
            ]);

            return back()->with('success', 'Assessment IKASANDI berhasil difinalisasi dan diajukan ke Admin Persandian.');
        }

        return back()->with('success', 'Jawaban berhasil disimpan sebagai draf sementara.');
    }

    public function verifyAssessment(Request $request, Assessment $assessment): RedirectResponse
    {
        $user = Auth::user();
        if (! $user->hasAnyRole(['Super Admin', 'Admin Persandian'])) {
            abort(403, 'Hanya Admin Persandian atau Super Admin yang dapat memverifikasi IKASANDI.');
        }

        $request->validate([
            'status' => 'required|in:verified,published,revising',
            'feedback' => 'nullable|string',
        ]);

        $assessment->update([
            'status' => $request->status,
            'verified_at' => now(),
        ]);

        return back()->with('success', 'Status Assessment berhasil diperbarui.');
    }

    public function printAssessment(Assessment $assessment): View
    {
        // Must be verified or published to be printed
        if (! in_array($assessment->status, ['verified', 'published'])) {
            abort(403, 'Assessment belum diverifikasi.');
        }

        $assessment->load('organization', 'answers.question.category');

        $categories = AssessmentCategory::with(['questions' => function ($q) {
            $q->where('is_active', true)->orderBy('order_num');
        }])->orderBy('order_num')->get();

        $existingAnswers = $assessment->answers()->pluck('answer', 'question_id')->toArray();

        return view('ikasandi.print', compact('assessment', 'categories', 'existingAnswers'));
    }

    public function destroy(Assessment $assessment): RedirectResponse
    {
        $user = Auth::user();
        if (! $user->hasAnyRole(['Super Admin', 'Admin Persandian'])) {
            abort(403, 'Unauthorized action.');
        }

        $assessment->answers()->delete();
        $assessment->delete();

        return back()->with('success', 'Data Assessment berhasil dihapus.');
    }
}
