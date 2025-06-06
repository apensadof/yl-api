<?php

namespace App\Command;

use App\Entity\Ceremonia;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:seed-ceremonias',
    description: 'Seed the database with ceremonias',
)]
class SeedCeremoniasCommand extends Command
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
        
        $io->title('Seeding Ceremonias Database');

        $ceremoniasData = [
            // Ceremonias Básicas
            [
                'nombre' => 'Consulta de Caracoles',
                'descripcion' => 'Primera consulta con caracoles para conocer el camino espiritual',
                'categoria' => 'basica',
                'requisitos' => 'Ninguno especial',
                'materiales' => ['caracoles', 'vela blanca', 'agua', 'flores blancas'],
                'procedimiento' => 'Consulta mediante el sistema de caracoles (diloggun)',
                'duracion_minutos' => 60
            ],
            [
                'nombre' => 'Registro de Entrada',
                'descripcion' => 'Ceremonia inicial para establecer la relación padrino-ahijado',
                'categoria' => 'basica',
                'requisitos' => 'Consulta previa',
                'materiales' => ['velas', 'flores', 'frutas', 'agua bendita'],
                'procedimiento' => 'Ceremonia de presentación a los Orishas',
                'duracion_minutos' => 90
            ],
            [
                'nombre' => 'Mano de Orula',
                'descripcion' => 'Ikofá para mujeres y Awofakan para hombres',
                'categoria' => 'basica',
                'requisitos' => 'Registro de entrada completo',
                'materiales' => ['ikines', 'manilla', 'collares', 'otanes'],
                'procedimiento' => 'Ceremonia de recibir la mano de Orula',
                'duracion_minutos' => 240
            ],
            [
                'nombre' => 'Guerreros',
                'descripcion' => 'Entrega de Elegguá, Oggún, Oshosi y Osun',
                'categoria' => 'basica',
                'requisitos' => 'Mano de Orula',
                'materiales' => ['otanes', 'caracol', 'llaves', 'herramientas de hierro', 'copa de plata'],
                'procedimiento' => 'Ceremonia de recibir los Orishas guerreros',
                'duracion_minutos' => 360
            ],
            [
                'nombre' => 'Collares de Protección',
                'descripcion' => 'Collares consagrados de los principales Orishas',
                'categoria' => 'basica',
                'requisitos' => 'Consulta de caracoles',
                'materiales' => ['cuentas de colores', 'hilo', 'omiero', 'sangre ritual'],
                'procedimiento' => 'Consagración de collares rituales',
                'duracion_minutos' => 180
            ],

            // Ceremonias Avanzadas
            [
                'nombre' => 'Iniciación en el Santo',
                'descripcion' => 'Kari-Osha, ceremonia de iniciación completa',
                'categoria' => 'avanzada',
                'requisitos' => 'Guerreros, collares, mínimo 1 año de preparación',
                'materiales' => ['otanes del orisha tutelar', 'soperas', 'herramientas', 'ropas rituales', 'corona'],
                'procedimiento' => 'Ceremonia completa de iniciación de 7 días',
                'duracion_minutos' => 10080 // 7 días
            ],
            [
                'nombre' => 'Cuarto de Santo',
                'descripcion' => 'Preparación del espacio sagrado personal',
                'categoria' => 'avanzada',
                'requisitos' => 'Iniciación en el Santo',
                'materiales' => ['soperas', 'herramientas', 'cortinas', 'altar', 'velas'],
                'procedimiento' => 'Consagración del espacio ritual personal',
                'duracion_minutos' => 240
            ],
            [
                'nombre' => 'Olokun',
                'descripcion' => 'Ceremonia de recibir al Orisha del océano',
                'categoria' => 'avanzada',
                'requisitos' => 'Iniciación, preparación especial',
                'materiales' => ['caracolas', 'otanes marinos', 'agua de mar', 'sopera azul'],
                'procedimiento' => 'Ceremonia de recepción de Olokun',
                'duracion_minutos' => 480
            ],
            [
                'nombre' => 'Iniciación en Ifá',
                'descripcion' => 'Ceremonia para convertirse en Babalawo',
                'categoria' => 'avanzada',
                'requisitos' => 'Mano de Orula, años de estudio, ser hombre',
                'materiales' => ['ikines', 'tablero de Ifá', 'herramientas de Orula', 'ropas sacerdotales'],
                'procedimiento' => 'Ceremonia completa de consagración como Babalawo',
                'duracion_minutos' => 4320 // 3 días
            ],
            [
                'nombre' => 'Cuchillo (Pinaldo)',
                'descripcion' => 'Ceremonia para recibir el derecho de sacrificio',
                'categoria' => 'avanzada',
                'requisitos' => 'Iniciación en el Santo, preparación especial',
                'materiales' => ['cuchillo ritual', 'otanes', 'animales para sacrificio'],
                'procedimiento' => 'Ceremonia de consagración para realizar sacrificios',
                'duracion_minutos' => 360
            ],

            // Ceremonias Especiales
            [
                'nombre' => 'Moyugba',
                'descripcion' => 'Ceremonia de saludo y respeto a los ancestros',
                'categoria' => 'especial',
                'requisitos' => 'Conocimiento básico',
                'materiales' => ['vela blanca', 'agua', 'flores'],
                'procedimiento' => 'Ritual de saludo a los muertos y Orishas',
                'duracion_minutos' => 30
            ],
            [
                'nombre' => 'Ebbo de Purificación',
                'descripcion' => 'Ritual de limpieza espiritual',
                'categoria' => 'especial',
                'requisitos' => 'Consulta que lo determine',
                'materiales' => ['hierbas específicas', 'velas', 'frutas', 'animales según el caso'],
                'procedimiento' => 'Ritual de limpieza según prescripción oracular',
                'duracion_minutos' => 120
            ],
            [
                'nombre' => 'Tambor de Fundamento',
                'descripcion' => 'Ceremonia con tambores sagrados para los Orishas',
                'categoria' => 'especial',
                'requisitos' => 'Iniciación, permisos ceremoniales',
                'materiales' => ['tambores consagrados', 'ropas rituales', 'ofrendas'],
                'procedimiento' => 'Ceremonia de tambor sagrado con posesiones',
                'duracion_minutos' => 480
            ]
        ];

        $io->info('Creating ' . count($ceremoniasData) . ' ceremonias...');

        $created = 0;
        $updated = 0;

        foreach ($ceremoniasData as $ceremoniaData) {
            // Check if ceremonia already exists
            $existingCeremonia = $this->entityManager->getRepository(Ceremonia::class)
                ->findByNombre($ceremoniaData['nombre']);

            if ($existingCeremonia) {
                // Update existing ceremonia
                $ceremonia = $existingCeremonia;
                $updated++;
            } else {
                // Create new ceremonia
                $ceremonia = new Ceremonia();
                $created++;
            }

            $ceremonia->setNombre($ceremoniaData['nombre']);
            $ceremonia->setDescripcion($ceremoniaData['descripcion']);
            $ceremonia->setCategoria($ceremoniaData['categoria']);
            $ceremonia->setRequisitos($ceremoniaData['requisitos'] ?? null);
            $ceremonia->setMateriales($ceremoniaData['materiales'] ?? []);
            $ceremonia->setProcedimiento($ceremoniaData['procedimiento'] ?? null);
            $ceremonia->setDuracionMinutos($ceremoniaData['duracion_minutos'] ?? null);
            $ceremonia->setUpdatedAt(new \DateTime());

            $this->entityManager->persist($ceremonia);
        }

        $this->entityManager->flush();

        $io->success([
            'Ceremonias seeded successfully!',
            'Created: ' . $created,
            'Updated: ' . $updated,
            'Total: ' . ($created + $updated)
        ]);

        return Command::SUCCESS;
    }
} 