<?php

namespace App\Security\Voter;

use App\Config\Roles;
use App\Entity\Post;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

class PostVoter extends Voter
{
    public const VIEW = 'view';
    public const EDIT = 'edit';
    public const DELETE = 'delete';

    private Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    protected function supports(string $attribute, $subject): bool
    {
        if ( ! in_array($attribute, [self::VIEW, self::EDIT, self::DELETE])) {
            return false;
        }

        // only vote on `Post` objects
        if ( ! $subject instanceof Post) {
            return false;
        }

        return true;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        /** @var User $user */
        $user = $token->getUser();

        if ( ! $user instanceof UserInterface) {
            return false;
        }

        if ($this->security->isGranted(Roles::Moderator->value)) {
            return true;
        }

        $post = $subject;

        return $this->canViewEdit($post, $user);
    }

    private function canViewEdit(Post $post, User $user): bool
    {
        return $user === $post->getUser();
    }
}
