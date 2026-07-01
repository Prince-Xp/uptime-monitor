<?php

namespace Tests\Feature;

use App\Jobs\CheckWebsiteJob;
use App\Mail\WebsiteDownMail;
use App\Models\Client;
use App\Models\Website;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class CheckWebsiteJobTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_healthy_site_stays_up_and_sends_no_mail(): void
    {
        Http::fake(['*' => Http::response('ok', 200)]);
        Mail::fake();

        $website = Website::factory()->create(['is_up' => true]);

        (new CheckWebsiteJob($website))->handle();

        $website->refresh();
        $this->assertTrue($website->is_up);
        Mail::assertNothingSent();
    }

    public function test_a_down_site_triggers_an_alert_with_correct_subject(): void
    {
        Http::fake(['*' => Http::response('error', 500)]);
        Mail::fake();

        $client = Client::factory()->create(['email' => 'client@example.com']);
        $website = Website::factory()->for($client)->create([
            'url' => 'https://broken.example.com',
            'is_up' => true,
        ]);

        (new CheckWebsiteJob($website))->handle();

        $website->refresh();
        $this->assertFalse($website->is_up);

        Mail::assertSent(WebsiteDownMail::class, function (WebsiteDownMail $mail) {
            return $mail->hasTo('client@example.com')
                && $mail->envelope()->subject === 'https://broken.example.com is down!';
        });
    }

    public function test_it_does_not_resend_alert_while_site_remains_down(): void
    {
        Http::fake(['*' => Http::response('error', 500)]);
        Mail::fake();

       
        $website = Website::factory()->create(['is_up' => false]);

        (new CheckWebsiteJob($website))->handle();

        Mail::assertNothingSent();
    }
}