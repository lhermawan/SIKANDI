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

        $avgScore = round(Assessment::avg('compliance_score') ?? 0, 1);
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

        return view('ikasandi.dashboard', compact(
            'totalOpd',
            'assessedCount',
            'avgScore',
            'avgRisk',
            'riskStats',
            'openIncidents',
            'websiteIssues',
            'assessments'
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
}
