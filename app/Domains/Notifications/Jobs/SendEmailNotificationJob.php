<?php

namespace App\Domains\Notifications\Jobs;

use App\Domains\Settings\Services\MailSettingsService;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Sends a bilingual HTML email notification using the SMTP credentials
 * stored in the `settings` table (configurable via the admin panel).
 *
 * Falls back gracefully if SMTP is not configured.
 */
class SendEmailNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 60;

    public function __construct(
        private readonly int $userId,
        private readonly string $eventKey,
        private readonly string $title,
        private readonly string $body,
        /** @var array<string, mixed> */
        private readonly array $data = [],
    ) {}

    public function handle(): void
    {
        $user = User::find($this->userId);
        if (! $user || ! $user->email) {
            return;
        }

        if (! MailSettingsService::apply()) {
            Log::info("SendEmailNotificationJob: SMTP not configured — skipping email to user {$this->userId}");

            return;
        }

        $title = $this->title;
        $body = $this->body;
        $event = $this->eventKey;
        $data = $this->data;

        try {
            Mail::html($this->buildHtml($title, $body, $data), function ($message) use ($user, $title) {
                $message->to($user->email, $user->name)->subject($title);
            });
        } catch (\Throwable $e) {
            Log::error("SendEmailNotificationJob failed for user {$this->userId}: ".$e->getMessage());
            $this->fail($e);
        }
    }

    /**
     * Build a bilingual NGO-branded HTML email.
     *
     * @param  array<string, mixed>  $data
     */
    private function buildHtml(string $title, string $body, array $data): string
    {
        $caseNo = htmlspecialchars((string) ($data['case_no'] ?? ''), ENT_QUOTES, 'UTF-8');
        $titleE = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
        $bodyE = htmlspecialchars($body, ENT_QUOTES, 'UTF-8');
        $appUrl = config('app.url', 'https://ggvt.org');
        $year = date('Y');

        return <<<HTML
        <!DOCTYPE html>
        <html lang="gu" dir="ltr">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>{$titleE}</title>
            <style>
                body { margin:0; padding:0; background:#FDFBF7; font-family:'Segoe UI',Helvetica,Arial,sans-serif; }
                .wrapper { max-width:600px; margin:0 auto; background:#FFFFFF; border-radius:12px; overflow:hidden; border:1px solid #E7E0D6; }
                .header { background:linear-gradient(135deg,#B45309 0%,#92400E 100%); padding:32px 24px; text-align:center; }
                .header h1 { color:#FFFFFF; margin:0; font-size:22px; font-weight:700; letter-spacing:-0.5px; }
                .header p { color:#FEF3C7; margin:4px 0 0; font-size:13px; }
                .body { padding:32px 24px; }
                .title { font-size:18px; font-weight:700; color:#1C1917; margin:0 0 12px; }
                .msg { font-size:15px; color:#44403C; line-height:1.6; margin:0 0 24px; }
                .case-chip { display:inline-block; background:#FEF3C7; color:#92400E; border:1px solid #F59E0B; border-radius:20px; padding:4px 14px; font-size:13px; font-weight:600; margin-bottom:24px; }
                .cta { display:inline-block; background:#B45309; color:#FFFFFF !important; text-decoration:none; padding:12px 28px; border-radius:8px; font-weight:600; font-size:15px; }
                .footer { background:#F5F0E8; padding:20px 24px; text-align:center; }
                .footer p { color:#78716C; font-size:12px; margin:4px 0; }
                .divider { height:1px; background:#E7E0D6; margin:24px 0; }
            </style>
        </head>
        <body>
            <div style="padding:24px 16px;">
                <div class="wrapper">
                    <div class="header">
                        <h1>🤝 Tribal Helping Hand</h1>
                        <p>Global Gramin Vikas Trust (GGVT)</p>
                    </div>
                    <div class="body">
                        <p class="title">{$titleE}</p>
                        <p class="msg">{$bodyE}</p>
                        {$this->caseChip($caseNo)}
                        <a href="{$appUrl}" class="cta">View Your Application →</a>
                        <div class="divider"></div>
                        <p style="font-size:13px;color:#78716C;">If you have questions, contact our helpline.</p>
                    </div>
                    <div class="footer">
                        <p><strong>Global Gramin Vikas Trust (GGVT)</strong></p>
                        <p>ट्राइबल हेल्पिंग हैंड • ট্রাইবাল হেল্পিং হ্যান্ড • Tribal Helping Hand</p>
                        <p style="color:#A8A29E;">&copy; {$year} GGVT. All rights reserved.</p>
                    </div>
                </div>
            </div>
        </body>
        </html>
        HTML;
    }

    private function caseChip(string $caseNo): string
    {
        if (empty($caseNo)) {
            return '';
        }

        return "<div><span class=\"case-chip\">Case: {$caseNo}</span></div>";
    }
}
