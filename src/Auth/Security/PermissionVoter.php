<?php
namespace App\Auth\Security;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

use App\Permissions\Models\Permission;
use App\Users\Models\User;


class PermissionVoter extends Voter
{
    protected function supports(string $attribute, mixed $subject): bool
    {
        return (bool) preg_match('/^[a-z]+_[a-z_]+$/', $attribute);
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User || $user->role() === null) {
            return false;
        }

        return $user->role()->getPermissions()->exists(
            fn (int $i, Permission $permission) => $permission->getName() === $attribute
        );
    }
}