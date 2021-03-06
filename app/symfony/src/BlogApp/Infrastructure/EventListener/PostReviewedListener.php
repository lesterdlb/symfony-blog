<?php

declare(strict_types=1);

namespace App\BlogApp\Infrastructure\EventListener;

use App\BlogApp\Domain\Event\PostReviewed;

use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class PostReviewedListener
{
    private MailerInterface $mailer;
    private LoggerInterface $logger;

    public function __construct(MailerInterface $mailer, LoggerInterface $logger)
    {
        $this->mailer = $mailer;
        $this->logger = $logger;
    }

    public function __invoke(PostReviewed $event): void
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

        $this->logger->info(
            'Email sent to user',
            ['Email' => $user->getEmail()]
        );
    }

}