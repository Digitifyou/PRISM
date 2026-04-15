<?php

namespace App\Jobs\Level1;

use App\Jobs\Level2\GenerateImageJob;
use App\Models\ContentPlan;
use App\Services\Level1\PlannerService;
use App\Services\Level1\ResearcherService;
use App\Services\Level1\StrategistService;
use App\Services\Level1\WriterService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class RunContentPlanJob implements ShouldQueue
{
    use Queueable;

    public int $tries   = 3;
    public int $timeout = 300;

    public function __construct(public ContentPlan $plan) {}

    public function handle(
        PlannerService    $planner,
        ResearcherService $researcher,
        StrategistService $strategist,
        WriterService     $writer,
    ): void {
        Log::info("RunContentPlanJob started", ['plan_id' => $this->plan->id]);

        // Step 1: Generate topic list
        $topics = $planner->generate($this->plan);

        // Step 2: For each topic → research → strategy → write → generate image
        foreach ($topics as $topic) {
            try {
                $research = $researcher->research($topic);
                $strategy = $strategist->analyze($this->plan, $topic, $research);
                $posts    = $writer->write($this->plan, $topic, $research, $strategy);

                // Dispatch image generation for each created post (staggered to avoid rate limits)
                foreach ($posts as $i => $post) {
                    GenerateImageJob::dispatch($post)->delay(now()->addSeconds($i * 8));
                }

                Log::info("Posts + image jobs created for topic: {$topic}");
            } catch (\Throwable $e) {
                Log::error("Failed on topic [{$topic}]: " . $e->getMessage());
            }
        }

        Log::info("RunContentPlanJob completed", ['plan_id' => $this->plan->id]);
    }
}
