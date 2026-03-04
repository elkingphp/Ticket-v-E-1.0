<?php

namespace Modules\Educational\Application\Policies;

use Modules\Users\Domain\Models\User;
use Modules\Educational\Domain\Models\EvaluationForm;

/**
 * EvaluationFormPolicy
 *
 * All authorization for EvaluationForm operations flows through here.
 * allow_evaluator_types JSON on assignments is configuration ONLY —
 * this Policy is the AUTHORITATIVE guard for all access decisions.
 */
class EvaluationFormPolicy
{
    /**
     * Who can see the list of forms (index page)?
     * Requires the manage permission — trainees cannot see the admin UI.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('education.evaluations.manage')
            || $user->can('education.evaluations.view');
    }

    /**
     * Who can view a single form (preview)?
     * Managers and admins only.
     */
    public function view(User $user, EvaluationForm $form): bool
    {
        return $user->can('education.evaluations.manage')
            || $user->can('education.evaluations.view');
    }

    /**
     * Who can create new forms?
     * Admin/manager only — trainees cannot create supervisory forms.
     */
    public function create(User $user): bool
    {
        return $user->can('education.evaluations.manage');
    }

    /**
     * Who can update a form?
     * Only admins, AND the form must still be in draft state.
     * Published/archived forms are frozen.
     */
    public function update(User $user, EvaluationForm $form): bool
    {
        return $user->can('education.evaluations.manage')
            && $form->canBeEdited();
    }

    /**
     * Who can publish a form?
     * Admin + form is draft + has at least 1 question.
     * (The model's publish() method also enforces the question check.)
     */
    public function publish(User $user, EvaluationForm $form): bool
    {
        return $user->can('education.evaluations.manage')
            && $form->status === 'draft'
            && $form->questions()->count() > 0;
    }

    /**
     * Who can archive a form?
     * Admin + form is currently published.
     */
    public function archive(User $user, EvaluationForm $form): bool
    {
        return $user->can('education.evaluations.manage')
            && $form->status === 'published';
    }

    /**
     * Who can delete a form?
     * Admin + form is draft + zero submitted evaluations.
     * (Uses model's canBeDeleted() guard.)
     */
    public function delete(User $user, EvaluationForm $form): bool
    {
        return $user->can('education.evaluations.manage')
            && $form->canBeDeleted();
    }

    /**
     * Who can view evaluation results?
     * Admins and managers — not regular trainees.
     */
    public function viewResults(User $user, EvaluationForm $form): bool
    {
        return $user->can('education.evaluations.manage')
            || $user->can('education.evaluations.results');
    }

    /**
     * Who can manage question CRUD within a form?
     * Admin + form must be in draft state.
     */
    public function manageQuestions(User $user, EvaluationForm $form): bool
    {
        return $user->can('education.evaluations.manage')
            && $form->canBeEdited();
    }
}
