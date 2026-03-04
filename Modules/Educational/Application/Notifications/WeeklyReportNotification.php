<?php

namespace Modules\Educational\Application\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WeeklyReportNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected array $stats
    ) {
    }

    public function via($notifiable): array
    {
        return ['mail']; // Weekly reports are usually best via Email
    }

    public function toMail($notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject('التقرير الأسبوعي لتقييم المحاضرات')
            ->greeting('مرحباً ' . ($notifiable->name ?? ''))
            ->line('إليك ملخص أداء المحاضرات خلال الأسبوع الماضي:')
            ->line('إجمالي التقييمات الجديدة: ' . $this->stats['new_evaluations_count'])
            ->line('المتوسط العام للأسبوع: ' . $this->stats['weekly_avg'])
            ->line('عدد المحاضرات التي تم تقييمها: ' . $this->stats['lectures_evaluated_count']);

        if ($this->stats['red_flags_count'] > 0) {
            $mail->line('تنبيه: تم رصد ' . $this->stats['red_flags_count'] . ' مؤشرات سلبية تحتاج مراجعة.')
                ->error();
        }

        return $mail
            ->action('عرض التقارير التفصيلية', route('educational.evaluations.forms.index'))
            ->line('شكراً لمتابعتكم الدائمة.');
    }
}
