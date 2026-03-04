<?php

namespace Modules\Educational\Application\Policies;

use Modules\Users\Domain\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Modules\Educational\Domain\Models\TraineeProfile;

class TraineeProfilePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view sensitive data of the model.
     * LAYER 3 SECURITY: Permission Gate
     */
    public function viewSensitiveData(User $user, TraineeProfile $traineeProfile): bool
    {
        // 1. A user can view their own sensitive data
        if ($user->id === $traineeProfile->user_id) {
            return true;
        }

        // 2. Or they specifically have the permission (SuperAdmin, Registrar usually)
        return $user->hasPermissionTo('education.view_sensitive_data');
    }
}
