<?php

namespace Modules\Tickets\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;
use Modules\Tickets\Domain\Models\TicketEmailTemplate;

class TicketEmailTemplateController extends Controller
{
    public function index()
    {
        $templates = TicketEmailTemplate::all();
        return view('tickets::admin.templates.index', compact('templates'));
    }

    public function create()
    {
        return view('tickets::admin.templates.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'event_key' => 'required|string|unique:tickets.ticket_email_templates,event_key',
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
        ]);

        TicketEmailTemplate::create($request->all());

        return redirect()->route('admin.tickets.templates.index')
            ->with('success', __('Template created successfully.'));
    }

    public function edit(TicketEmailTemplate $template)
    {
        return view('tickets::admin.templates.edit', compact('template'));
    }

    public function update(Request $request, TicketEmailTemplate $template)
    {
        $request->validate([
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
        ]);

        // Event key is generally not editable as it is tied to system events
        $template->update($request->only('subject', 'body'));

        return redirect()->route('admin.tickets.templates.index')
            ->with('success', __('tickets::messages.messages.updated'));
    }

    public function destroy(TicketEmailTemplate $template)
    {
        $template->delete();

        return redirect()->route('admin.tickets.templates.index')
            ->with('success', __('tickets::messages.messages.deleted'));
    }

    public function preview(TicketEmailTemplate $template)
    {
        $body = $template->body;

        $placeholders = [
            '{ticket_id}' => 'TICK-2024-001',
            '{subject}' => 'مشكلة في الولوج إلى المنصة',
            '{user_name}' => 'أحمد محمد',
            '{url}' => route('tickets.show', 1),
        ];

        $content = str_replace(array_keys($placeholders), array_values($placeholders), $body);

        // Simple wrapper for preview
        return "
            <div style='font-family: sans-serif; max-width: 600px; margin: 20px auto; padding: 20px; border: 1px solid #eee;'>
                <div style='margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid #405189;'>
                    <h2 style='margin:0; color: #405189;'>{$template->subject}</h2>
                </div>
                <div style='line-height: 1.6;'>
                    {$content}
                </div>
            </div>
        ";
    }

    public function test(Request $request, TicketEmailTemplate $template)
    {
        $request->validate([
            'email' => 'required|email'
        ]);

        try {
            // Apply SMTP settings from database dynamically
            $this->applyMailSettings();

            $body = $template->body;
            $placeholders = [
                '{ticket_id}' => 'TEST-001',
                '{subject}' => 'Test Subject',
                '{user_name}' => 'Test User',
                '{url}' => url('/'),
            ];
            $content = str_replace(array_keys($placeholders), array_values($placeholders), $body);

            // Use the configured mailer
            Mail::html($content, function ($message) use ($request, $template) {
                $message->to($request->email)
                    ->subject('[TEST] ' . $template->subject);

                $fromAddress = get_setting('mail_from_address', config('mail.from.address'));
                $fromName = get_setting('mail_from_name', config('mail.from.name'));
                $message->from($fromAddress, $fromName);
            });

            return response()->json([
                'success' => true,
                'message' => __('tickets::messages.test_sent_success')
            ]);
        } catch (\Exception $e) {
            \Log::error('Email Test Failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Email Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Dynamically apply mail settings from the settings table
     */
    private function applyMailSettings()
    {
        $mailer = get_setting('mail_mailer', 'smtp');

        Config::set('mail.default', $mailer);
        Config::set('mail.mailers.smtp.host', get_setting('mail_host', config('mail.mailers.smtp.host')));
        Config::set('mail.mailers.smtp.port', get_setting('mail_port', config('mail.mailers.smtp.port')));
        Config::set('mail.mailers.smtp.encryption', get_setting('mail_encryption', config('mail.mailers.smtp.encryption')));
        Config::set('mail.mailers.smtp.username', get_setting('mail_username', config('mail.mailers.smtp.username')));
        Config::set('mail.mailers.smtp.password', get_setting('mail_password', config('mail.mailers.smtp.password')));

        Config::set('mail.from.address', get_setting('mail_from_address', config('mail.from.address')));
        Config::set('mail.from.name', get_setting('mail_from_name', config('mail.from.name')));
    }
}
