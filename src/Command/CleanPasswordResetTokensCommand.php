<?php

namespace App\Command;

use App\Repository\PasswordResetTokenRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:clean-password-reset-tokens',
    description: 'Clean expired password reset tokens from the database',
)]
class CleanPasswordResetTokensCommand extends Command
{
    private PasswordResetTokenRepository $passwordResetTokenRepository;

    public function __construct(PasswordResetTokenRepository $passwordResetTokenRepository)
    {
        $this->passwordResetTokenRepository = $passwordResetTokenRepository;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setHelp('This command removes all expired password reset tokens from the database to keep it clean.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $deletedCount = $this->passwordResetTokenRepository->cleanExpiredTokens();

            if ($deletedCount > 0) {
                $io->success(sprintf('Successfully deleted %d expired password reset token(s)', $deletedCount));
            } else {
                $io->info('No expired password reset tokens found');
            }

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $io->error('Error cleaning password reset tokens: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
} 