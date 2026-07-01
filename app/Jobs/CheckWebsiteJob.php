<?php

namespace App\Jobs;

use App\Mail\WebsiteDownMail;
use App\Models\Website;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Throwable;

class CheckWebsiteJob implements ShouldQueue
{
    use Queueable;

    public function __construct(public Website $website)
    {
    }

    public function handle(): void
    {
        $wasUp = $this->website->is_up;
        $isUp = $this->performCheck();

        $this->website->is_up = $isUp;
        $this->website->last_checked_at = now();

        if (! $isUp && $wasUp) {
            Mail::to($this->website->client->email)->send(new WebsiteDownMail($this->website));
            $this->website->last_alerted_at = now();
        }

        $this->website->save();
    }

    private function performCheck(): bool
    {
        try {
            $response = Http::timeout(10)->connectTimeout(10)->get($this->website->url);

            return $response->successful();
        } catch (ConnectionException|Throwable $e) {
            return false;
        }
    }
}