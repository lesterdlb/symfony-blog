<?php

namespace App\EventListener;

use App\Event\PostReviewed;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class PostReviewedListener
{
    private MailerInterface $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    public function __invoke(PostReviewed $event)
    {
        $post = $event->getPost();
        $user = $post->getUser();

        $body = <<<BODY
        <p>Hello {$user->getName()}!</p>
        <p>We would like to inform you that your post: "{$post->getTitle()}" has been {$post->getStatus()}</p>
        <p></p>
        BODY;

        $email = (new Email())
            ->from($event->getModeratorEmail())
            ->to($user->getEmail())
            ->subject(sprintf('You post has been %s', $post->getStatus()))
            ->html($body);

        $this->mailer->send($email);
    }

}