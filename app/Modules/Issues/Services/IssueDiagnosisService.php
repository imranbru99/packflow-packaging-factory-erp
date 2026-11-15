<?php

namespace App\Modules\Issues\Services;

use App\Modules\Issues\Models\Issue;
use App\Modules\Issues\Models\KnowledgeArticle;
use ImranDevBd\AiHub\Facades\AIHub;

class IssueDiagnosisService
{
    public function diagnose(Issue $issue): Issue
    {
        $article = $this->matchArticle($issue);
        $local = $article
            ? $article->title."\n\n".$article->solution
            : $this->fallback($issue);

        $suggestion = $local;
        $provider = 'packflow-rules';

        try {
            if ($this->aiConfigured()) {
                $response = AIHub::prompt($this->prompt($issue, $local))
                    ->system('You are a packaging converting plant engineer. Give a short root cause and the next three actions. Plain text.')
                    ->generate();

                if ($response->success && trim($response->content) !== '') {
                    $suggestion = trim($response->content);
                    $provider = $response->provider;
                }
            }
        } catch (\Throwable) {
            $provider = 'packflow-rules';
        }

        $issue->ai_suggestion = $suggestion;
        $issue->ai_provider = $provider;
        $issue->save();

        return $issue;
    }

    protected function matchArticle(Issue $issue): ?KnowledgeArticle
    {
        $haystack = strtolower($issue->category.' '.$issue->title.' '.$issue->description);

        return KnowledgeArticle::query()->get()->first(function (KnowledgeArticle $article) use ($haystack) {
            foreach (preg_split('/\W+/', strtolower($article->title.' '.$article->category)) as $word) {
                if (strlen($word) > 4 && str_contains($haystack, $word)) {
                    return true;
                }
            }

            return false;
        });
    }

    protected function fallback(Issue $issue): string
    {
        return match ($issue->category) {
            'machine_breakdown' => 'Stop the deck, lock out the machine, and check the last three downtime reasons on this asset before calling for a spare. Look at bearings, doctor blades, and tension before replacing the whole unit.',
            'material_shortage' => 'The job was released with a shortage. Split the run to the available reel weight, raise a purchase suggestion, and do not start makeready on a partial reel if the splice count would exceed the customer spec.',
            'quality_reject' => 'Hold the lot. Compare print density, dimensions, and bond against the last accepted sample. Tie the defect to the reel, machine, and operator before the next shift uses the same setup.',
            'process_delay' => 'The stage is past its slot. Check whether the previous centre is waiting on ink, plates, or a shared die. Group the next jobs with the same flute and colour count to avoid another changeover.',
            'estimation_error' => 'Re-run the blank. For an RSC, blank length is 2×(L+W)+joint and blank width is H+W. Confirm flute take-up and the waste percent before the quotation is accepted.',
            default => 'Record the lot, machine, and shift. Contain the material, then compare this job with the last good run of the same structure.',
        };
    }

    protected function prompt(Issue $issue, string $local): string
    {
        return <<<TXT
        Category: {$issue->category}
        Severity: {$issue->severity}
        Title: {$issue->title}
        Detail: {$issue->description}

        Plant knowledge already on file:
        {$local}
        TXT;
    }

    protected function aiConfigured(): bool
    {
        $provider = config('ai-hub.default');
        $key = config("ai-hub.providers.{$provider}.key")
            ?? config("ai-hub.providers.{$provider}.api_key")
            ?? env('AI_HUB_API_KEY');

        return is_string($key) && $key !== '';
    }
}
