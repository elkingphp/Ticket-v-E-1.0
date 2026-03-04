<?php

namespace Modules\Educational\Application\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Modules\Educational\Domain\Models\LectureFormAssignment;

class RedFlagAlertNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected LectureFormAssignment $assignment,
        protected array $flaggedQuestions
    ) {
    }

    public function via($notifiable): array
    {
        $settings = app(\Modules\Educational\Application\Services\EvaluationSettings::class);

        if (!$settings->isRedFlagEnabled()) {
            return [];
        }

        return $settings->notificationChannels();
    }

    public function toMail($notifiable): MailMessage
    {
        $lecture = $this->assignment->lecture;
        $form = $this->assignment->form;

        $mail = (new MailMessage)
            ->error()
            ->subject('تنبييه: مؤشر أحمر في تقييم المحاضرة - ' . $lecture->title)
            ->greeting('تنبيه إداري')
            ->line('نود إحاطتكم بوجود تدني في نتائج تقييم المراقبين للمحاضرة: ' . $lecture->title)
            ->line('النموذج المستخدم: ' . $form->title)
            ->line('الأسئلة التي سجلت أقل من 3.0:');

        foreach ($this->flaggedQuestions as $flag) {
            $mail->line('- ' . $flag['question'] . ' (المتوسط: ' . $flag['observer_avg'] . ')');
        }

        return $mail
            ->action('عرض لوحة النتائج', route('educational.evaluations.forms.results', $form))
            ->line('يرجى مراجعة النتائج واتخاذ الإجراء اللازم.');
    }

    public function toArray($notifiable): array
    {
        return [
            'assignment_id' => $this->assignment->id,
            'lecture_id' => $this->assignment->lecture_id,
            'lecture_title' => $this->assignment->lecture?->title,
            'flagged_count' => count($this->flaggedQuestions),
            'type' => 'red_flag',
            'severity' => 'high',
            'message' => 'تنبيه: مؤشرات سلبية في تقييم محاضرة ' . $this->assignment->lecture?->title,
            'action_url' => route('educational.evaluations.forms.results', $this->assignment->form_id),
        ];
    }
}
