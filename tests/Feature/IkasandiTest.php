<?php

namespace Tests\Feature;

use App\Models\Assessment;
use App\Models\AssessmentQuestion;
use App\Models\Organization;
use App\Models\User;
use Tests\TestCase;

class IkasandiTest extends TestCase
{
    public function test_ikasandi_dashboard_loads(): void
    {
        $admin = User::where('email', 'admin.sandi@ciamis.go.id')->first();

        $response = $this->actingAs($admin)->get('/ikasandi/dashboard');
        $response->assertStatus(200);
        $response->assertSee('IKASANDI');
    }

    public function test_assessment_scoring_engine(): void
    {
        $org = Organization::first();
        $assessment = Assessment::create([
            'organization_id' => $org->id,
            'title' => 'Test Assessment '.date('Y'),
            'year' => date('Y'),
            'period' => 'Tahunan',
            'status' => 'draft',
        ]);

        $questions = AssessmentQuestion::take(4)->get();
        $this->assertNotEmpty($questions);

        // Answer questions: 2 compliant (100), 1 partial (50), 1 non_compliant (0)
        $assessment->answers()->create([
            'question_id' => $questions[0]->id,
            'answer' => 'compliant',
            'score' => 100,
        ]);
        $assessment->answers()->create([
            'question_id' => $questions[1]->id,
            'answer' => 'compliant',
            'score' => 100,
        ]);
        $assessment->answers()->create([
            'question_id' => $questions[2]->id,
            'answer' => 'partial',
            'score' => 50,
        ]);
        $assessment->answers()->create([
            'question_id' => $questions[3]->id,
            'answer' => 'non_compliant',
            'score' => 0,
        ]);

        $assessment->recalculateScores();

        // Average = (100 + 100 + 50 + 0) / 4 = 62.5
        $this->assertEquals(62.5, $assessment->compliance_score);
        // Risk score = 100 - 62.5 = 37.5
        $this->assertEquals(37.5, $assessment->risk_score);
    }
}
