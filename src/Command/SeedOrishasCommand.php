<?php

namespace App\Command;

use App\Entity\Orisha;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:seed-orishas',
    description: 'Seed the database with orishas from the JSON file',
)]
class SeedOrishasCommand extends Command
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        $io->title('Seeding Orishas Database');

        // Read the JSON file
        $jsonPath = __DIR__ . '/../../orishas.json';
        if (!file_exists($jsonPath)) {
            $io->error('orishas.json file not found at: ' . $jsonPath);
            return Command::FAILURE;
        }

        $jsonContent = file_get_contents($jsonPath);
        $orishasData = json_decode($jsonContent, true);

        if (!$orishasData) {
            $io->error('Failed to parse orishas.json file');
            return Command::FAILURE;
        }

        $io->info('Found ' . count($orishasData) . ' orishas in JSON file');

        $created = 0;
        $updated = 0;

        foreach ($orishasData as $orishaData) {
            // Check if orisha already exists
            $existingOrisha = $this->entityManager->getRepository(Orisha::class)
                ->findByNombre($orishaData['nombre']);

            if ($existingOrisha) {
                // Update existing orisha
                $orisha = $existingOrisha;
                $updated++;
            } else {
                // Create new orisha
                $orisha = new Orisha();
                $created++;
            }

            $orisha->setNombre($orishaData['nombre']);
            $orisha->setOtrosNombres($orishaData['otros_nombres'] ?? []);
            $orisha->setDominio($orishaData['dominio']);
            $orisha->setColor($orishaData['color']);
            $orisha->setNumero($orishaData['numero']);
            $orisha->setAtributos($orishaData['atributos'] ?? []);
            $orisha->setSincretismo($orishaData['sincretismo']);
            $orisha->setDia($orishaData['dia']);
            $orisha->setCategoria($orishaData['categoria'] ?? null);
            $orisha->setUpdatedAt(new \DateTime());

            $this->entityManager->persist($orisha);

            // Flush every 10 entities to avoid memory issues
            if (($created + $updated) % 10 === 0) {
                $this->entityManager->flush();
                $io->writeln('Processed ' . ($created + $updated) . ' orishas...');
            }
        }

        // Final flush
        $this->entityManager->flush();

        $io->success([
            'Orishas seeded successfully!',
            'Created: ' . $created,
            'Updated: ' . $updated,
            'Total: ' . ($created + $updated)
        ]);

        return Command::SUCCESS;
    }
} 