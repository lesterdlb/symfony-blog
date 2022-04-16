<?php

namespace App\BlogApp\Infrastructure\Command;

use App\BlogApp\Infrastructure\Persistence\Repository\UserRepository;
use App\Config\Roles;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'user:change:role',
    description: 'Change a user role',
)]
class UserChangeRoleCommand extends Command
{
    private UserRepository $userRepository;
    private LoggerInterface $logger;

    public function __construct(UserRepository $userRepository, LoggerInterface $logger)
    {
        $this->userRepository = $userRepository;
        $this->logger         = $logger;

        parent::__construct();
    }

    protected function configure(): void
    {
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io     = new SymfonyStyle($input, $output);
        $helper = $this->getHelper('question');

        $question = new Question('Please enter the user email: ');
        $email    = $helper->ask($input, $output, $question);

        $user = $this->userRepository->findOneByEmail($email);

        if ( ! $user) {
            $io->error(sprintf('The user "%s" does not exists.', $email));

            $this->logger->error(
                sprintf(
                    'User email not found: %s',
                    $email
                )
            );

            return Command::FAILURE;
        }

        $roles    = array_map(fn(Roles $role) => $role->value, Roles::cases());
        $question = new Question('Please enter the new Role: ');
        $io->info(vsprintf('Current Role: %s', $user->getRoles()));
        $io->title('Available Roles: ');
        $io->listing($roles);
        $role = $helper->ask($input, $output, $question);

        if ( ! in_array($role, $roles)) {
            $io->error(sprintf('The Role "%s" does not exists.', $role));

            $this->logger->error(
                sprintf(
                    'Invalid role change attempt -> User: %s, Role: %s',
                    $user->getUserIdentifier(),
                    $role
                )
            );

            return Command::FAILURE;
        }

        $user->setRoles([$role]);
        $this->userRepository->add($user);

        $io->success('Role changed successfully.');

        $this->logger->info(
            sprintf(
                'The user %s changed role to %s.',
                $user->getUserIdentifier(),
                $role
            )
        );

        return Command::SUCCESS;
    }
}
