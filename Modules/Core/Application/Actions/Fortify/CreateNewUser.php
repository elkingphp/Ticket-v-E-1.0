<?php

namespace Modules\Core\Application\Actions\Fortify;

use Modules\Users\Domain\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * SECURITY: Public self-registration is DISABLED for this system.
     * User accounts are created exclusively by administrators via the admin panel.
     * The registration route is kept active to avoid RouteNotFoundException in views,
     * but any attempt to actually register is rejected here.
     *
     * @param  array<string, string>  $input
     * @throws ValidationException
     */
    public function create(array $input): User
    {
        // Reject all public registration attempts.
        // Administrators should create accounts via: /users/create
        throw ValidationException::withMessages([
            'email' => [
                __('auth.registration_disabled', [
                    'default' => 'التسجيل الذاتي غير مسموح به. يُرجى التواصل مع المسؤول لإنشاء حسابك.',
                ])
            ],
        ]);
    }
}
