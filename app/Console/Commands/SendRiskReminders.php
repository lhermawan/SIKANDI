<?php

namespace App\Console\Commands;

use App\Models\Risk;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

#[Signature('risk:reminders')]
#[Description('Send reminders for overdue or approaching risk treatments')]
class SendRiskReminders extends Command
{
    public function handle()
    {
        $this->info('Checking for risk treatment reminders...');

        $overdueRisks = Risk::where('status', 'open')
            ->whereNotNull('due_date')
            ->whereDate('due_date', '<', now())
            ->get();

        foreach ($overdueRisks as $risk) {
            // Check if treatments are 100% complete
            $hasIncompleteTreatments = $risk->treatments()
                ->where(function ($q) {
                    $q->whereNull('progress_percent')
                        ->orWhere('progress_percent', '<', 100);
                })->exists();

            if ($hasIncompleteTreatments || $risk->treatments->isEmpty()) {
                $owner = $risk->owner ? $risk->owner->name : 'Unassigned';
                $message = "Risk {$risk->risk_code} ({$risk->title}) is overdue! Due date was {$risk->due_date->format('Y-m-d')}. Owner: {$owner}";

                Log::warning($message);
                $this->warn($message);

                // TODO: Dispatch Mailable or WhatsApp notification job here
                // e.g., Mail::to($risk->owner->email)->send(new OverdueRiskMail($risk));
            }
        }

        $this->info('Found '.$overdueRisks->count().' overdue risks. Notifications logged.');
    }
}
