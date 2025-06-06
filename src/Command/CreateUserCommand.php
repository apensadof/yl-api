<?php

namespace App\Command;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-user',
    description: 'Create a new user for the Yorubas Latinos API',
)]
class CreateUserCommand extends Command
{
    private EntityManagerInterface $entityManager;
    private UserPasswordHasherInterface $passwordHasher;
    private UserRepository $userRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        UserPasswordHasherInterface $passwordHasher,
        UserRepository $userRepository
    ) {
        $this->entityManager = $entityManager;
        $this->passwordHasher = $passwordHasher;
        $this->userRepository = $userRepository;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('name', InputArgument::OPTIONAL, 'User full name')
            ->addArgument('email', InputArgument::OPTIONAL, 'User email')
            ->addArgument('password', InputArgument::OPTIONAL, 'User password')
            ->addOption('role', 'r', InputOption::VALUE_OPTIONAL, 'User role', 'babalawo')
            ->setHelp('This command allows you to create a user for the API...')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // Get user input
        $name = $input->getArgument('name');
        $email = $input->getArgument('email');
        $password = $input->getArgument('password');
        $role = $input->getOption('role');

        // Interactive mode if arguments not provided
        if (!$name) {
            $name = $io->ask('Enter user full name');
        }

        if (!$email) {
            $email = $io->ask('Enter user email');
        }

        if (!$password) {
            $password = $io->askHidden('Enter user password (input will be hidden)');
        }

        // Validate inputs
        if (empty($name) || strlen($name) < 2) {
            $io->error('Name must be at least 2 characters long');
            return Command::FAILURE;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $io->error('Invalid email format');
            return Command::FAILURE;
        }

        if (strlen($password) < 6) {
            $io->error('Password must be at least 6 characters long');
            return Command::FAILURE;
        }

        // Check if user already exists
        $existingUser = $this->userRepository->findByEmail($email);
        if ($existingUser) {
            $io->error('User with this email already exists');
            return Command::FAILURE;
        }

        // Validate role
        $validRoles = ['babalawo', 'iyanifa', 'admin'];
        if (!in_array($role, $validRoles)) {
            $io->error('Invalid role. Valid roles: ' . implode(', ', $validRoles));
            return Command::FAILURE;
        }

        // Create user
        $user = new User();
        $user->setName($name);
        $user->setEmail($email);
        $user->setRole($role);

        // Hash password
        $hashedPassword = $this->passwordHasher->hashPassword($user, $password);
        $user->setPasswordHash($hashedPassword);

        // Save to database
        try {
            $this->entityManager->persist($user);
            $this->entityManager->flush();

            $io->success('User created successfully!');
            $io->table(
                ['Field', 'Value'],
                [
                    ['ID', $user->getId()],
                    ['Name', $user->getName()],
                    ['Email', $user->getEmail()],
                    ['Role', $user->getRole()],
                    ['Created At', $user->getCreatedAt()->format('Y-m-d H:i:s')],
                ]
            );

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $io->error('Error creating user: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
} 