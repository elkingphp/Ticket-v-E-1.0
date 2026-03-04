<?php

namespace Modules\Educational\Application\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Modules\Educational\Domain\Models\LectureFormAssignment;

class LectureEvaluationAssigned extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected LectureFormAssignment $assignment,
        protected string $role
    ) {
    }

    public function via($notifiable): array
    {
        $settings = app(\Modules\Educational\Application\Services\EvaluationSettings::class);

        if (!$settings->isAssignmentNotificationEnabled()) {
            return [];
        }

        return $settings->notificationChannels();
    }

    public function toMail($notifiable): MailMessage
    {
        $lecture = $this->assignment->lecture;
        $form = $this->assignment->form;

        return (new MailMessage)
            ->subject('طلب تقييم محاضرة: ' . $lecture->title)
            ->greeting('مرحباً ' . ($notifiable->name ?? ''))
            ->line('تم تعيين نموذج تقييم جديد للمحاضرة: ' . $lecture->title)
            ->line('النموذج: ' . $form->title)
            ->line('دورك في التقييم: ' . ($this->role === 'trainee' ? 'متدرب' : 'مراقب'))
            ->action('تعبئة التقييم الآن', route('educational.evaluations.assignments.fill', $this->assignment))
            ->line('شكراً لمشاركتك في تحسين جودة التعليم.');
    }

    public function toArray($notifiable): array
    {
        return [
            'assignment_id' => $this->assignment->id,
            'lecture_id' => $this->assignment->lecture_id,
            'lecture_title' => $this->assignment->lecture?->title,
            'form_title' => $this->assignment->form?->title,
            'role' => $this->role,
            'message' => 'تم طلب تقييمك لمحاضرة ' . $this->assignment->lecture?->title,
            'action_url' => route('educational.evaluations.assignments.fill', $this->assignment),
        ];
    }
}
