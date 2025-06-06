<?php

namespace App\Command;

use App\Repository\UserRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:list-users',
    description: 'List all users in the system',
)]
class ListUsersCommand extends Command
{
    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('role', 'r', InputOption::VALUE_OPTIONAL, 'Filter by role')
            ->addOption('limit', 'l', InputOption::VALUE_OPTIONAL, 'Limit number of results', 50)
            ->setHelp('This command lists all users in the system with optional filtering...')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        $role = $input->getOption('role');
        $limit = (int) $input->getOption('limit');

        try {
            // Get users based on filter
            if ($role) {
                $users = $this->userRepository->findBy(['role' => $role], ['created_at' => 'DESC'], $limit);
                $io->title("Users with role: {$role}");
            } else {
                $users = $this->userRepository->findBy([], ['created_at' => 'DESC'], $limit);
                $io->title('All Users');
            }

            if (empty($users)) {
                $io->warning('No users found');
                return Command::SUCCESS;
            }

            // Prepare data for table
            $tableData = [];
            foreach ($users as $user) {
                $tableData[] = [
                    $user->getId(),
                    $user->getName(),
                    $user->getEmail(),
                    $user->getRole(),
                    $user->getCreatedAt()->format('Y-m-d H:i:s'),
                ];
            }

            // Display table
            $io->table(
                ['ID', 'Name', 'Email', 'Role', 'Created At'],
                $tableData
            );

            $io->success(sprintf('Found %d user(s)', count($users)));

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $io->error('Error listing users: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
} 