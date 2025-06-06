<?php

namespace App\Command;

use App\Entity\Knowledge;
use App\Entity\KnowledgeCategory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:seed-knowledge',
    description: 'Seed the knowledge base with sample data'
)]
class SeedKnowledgeCommand extends Command
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

        // Create categories
        $categories = $this->createCategories();
        $io->success(sprintf('Created %d categories', count($categories)));

        // Create knowledge items
        $itemsCount = $this->createKnowledgeItems($categories);
        $io->success(sprintf('Created %d knowledge items', $itemsCount));

        $io->success('Knowledge base seeded successfully!');

        return Command::SUCCESS;
    }

    private function createCategories(): array
    {
        $categoriesData = [
            [
                'id' => 'odu',
                'name' => 'Odu de Ifá',
                'description' => 'Los signos fundamentales del sistema de adivinación de Ifá'
            ],
            [
                'id' => 'orishas',
                'name' => 'Orishas',
                'description' => 'Deidades yorubas y su conocimiento'
            ],
            [
                'id' => 'ceremonies',
                'name' => 'Ceremonias',
                'description' => 'Rituales y ceremonias tradicionales'
            ],
            [
                'id' => 'tools',
                'name' => 'Herramientas',
                'description' => 'Implementos sagrados y su uso'
            ],
            [
                'id' => 'prayers',
                'name' => 'Rezos y Cantos',
                'description' => 'Oraciones y cantos tradicionales'
            ],
            [
                'id' => 'glossary',
                'name' => 'Glosario',
                'description' => 'Términos y definiciones'
            ]
        ];

        $categories = [];
        
        foreach ($categoriesData as $categoryData) {
            $category = $this->entityManager->getRepository(KnowledgeCategory::class)->find($categoryData['id']);
            
            if (!$category) {
                $category = new KnowledgeCategory();
                $category->setId($categoryData['id']);
                $category->setName($categoryData['name']);
                $category->setDescription($categoryData['description']);
                
                $this->entityManager->persist($category);
                $categories[] = $category;
            }
        }

        $this->entityManager->flush();
        
        return $categories;
    }

    private function createKnowledgeItems(array $categories): int
    {
        $knowledgeData = [
            // Orishas
            [
                'id' => 'eleggua',
                'title' => 'Elegguá',
                'category' => 'orishas',
                'content' => 'Elegguá es el Orisha de los caminos, las oportunidades y el destino. Es el mensajero de los Orishas y el guardián de las puertas entre los mundos. Sin su permiso, ninguna comunicación con las otras deidades es posible. Es el primero en ser saludado en cualquier ceremonia y el último en despedirse. Elegguá abre y cierra los caminos, otorga oportunidades y remueve obstáculos. Se le representa como un niño travieso o un anciano sabio, y sus colores son el rojo y el negro.',
                'keywords' => ['orisha', 'caminos', 'oportunidades', 'guardián', 'mensajero', 'puertas']
            ],
            [
                'id' => 'yemaya',
                'title' => 'Yemayá',
                'category' => 'orishas',
                'content' => 'Yemayá es la madre de todos los Orishas, la diosa del mar y la maternidad. Es considerada la creadora de la vida y protectora de las mujeres, especialmente durante el embarazo y el parto. Sus aguas saladas son el origen de toda vida en la Tierra. Se le representa como una mujer hermosa con vestidos azules y blancos, y sus colores sagrados son el azul marino y el blanco. Es conocida por su amor incondicional y su protección maternal.',
                'keywords' => ['orisha', 'madre', 'mar', 'maternidad', 'vida', 'protección', 'agua']
            ],
            [
                'id' => 'shango',
                'title' => 'Shangó',
                'category' => 'orishas',
                'content' => 'Shangó es el Orisha del fuego, el rayo, el trueno, la guerra, la música y la danza. Es conocido por su carácter ardiente, su pasión y su justicia. Fue el cuarto rey de Oyó y es recordado por su valentía y poder. Shangó controla los elementos del fuego y la electricidad, y es invocado para obtener justicia y protección. Sus colores son el rojo y el blanco, y su símbolo es el hacha de doble filo (oshe).',
                'keywords' => ['orisha', 'fuego', 'rayo', 'trueno', 'justicia', 'guerra', 'música', 'rey']
            ],
            
            // Odu de Ifá
            [
                'id' => 'ejiogbe',
                'title' => 'Ejiogbe',
                'category' => 'odu',
                'content' => 'Ejiogbe es el primer Odu de Ifá, representado por ocho marcas verticales (IIIIIIII). Es considerado el padre de todos los demás Odus y simboliza la perfección, la completitud y el principio de todas las cosas. Este Odu habla de liderazgo, autoridad, sabiduría ancestral y la conexión directa con Olodumare. Las personas bajo este signo suelen tener características de líderes naturales y grandes responsabilidades espirituales.',
                'keywords' => ['odu', 'primero', 'liderazgo', 'perfección', 'autoridad', 'sabiduría', 'ancestral']
            ],
            [
                'id' => 'oyekumere',
                'title' => 'Oyeku Meyi',
                'category' => 'odu',
                'content' => 'Oyeku Meyi es el segundo Odu principal de Ifá, representado por dieciséis marcas en pares (II II II II II II II II). Este Odu se asocia con la muerte, la transformación, el renacimiento y los misterios del más allá. Habla de la necesidad de transformación espiritual y la importancia de honrar a los ancestros. También trata sobre la paciencia, la reflexión y la sabiduría que viene con la experiencia.',
                'keywords' => ['odu', 'muerte', 'transformación', 'renacimiento', 'ancestros', 'paciencia', 'sabiduría']
            ],
            
            // Ceremonias
            [
                'id' => 'moyugba',
                'title' => 'Moyugba',
                'category' => 'ceremonies',
                'content' => 'El Moyugba es una oración fundamental en la religión yoruba que se realiza al inicio de cualquier ceremonia o consulta. Es un saludo reverencial que reconoce a Olodumare (Dios supremo), a los Orishas, a los ancestros (Egun) y a los mayores espirituales. La palabra "Moyugba" significa "yo rindo homenaje" o "yo saludo con respeto". Esta ceremonia establece la conexión espiritual necesaria y pide permiso y bendiciones para proceder con cualquier trabajo espiritual.',
                'keywords' => ['ceremonia', 'oración', 'saludo', 'respeto', 'olodumare', 'orishas', 'ancestros', 'egun']
            ],
            [
                'id' => 'tambor',
                'title' => 'Tambor de Fundamento',
                'category' => 'ceremonies',
                'content' => 'El Tambor de Fundamento es una ceremonia sagrada donde se toca música ritual para honrar y llamar a los Orishas. Los tambores batá, sagrados y consagrados, son tocados por tamboleros iniciados siguiendo toques específicos para cada Orisha. Durante estas ceremonias, los Orishas pueden manifestarse a través de la posesión espiritual de los participantes. Es una de las formas más poderosas de comunicación con las deidades yorubas.',
                'keywords' => ['ceremonia', 'tambor', 'batá', 'música', 'ritual', 'orishas', 'posesión', 'manifestación']
            ],
            
            // Herramientas
            [
                'id' => 'caracoles',
                'title' => 'Caracoles (Diloggun)',
                'category' => 'tools',
                'content' => 'Los caracoles o Diloggun son una herramienta fundamental de adivinación en la religión yoruba. Consiste en 16 o 21 caracoles cowrie que se lanzan para obtener respuestas de los Orishas. Cada configuración de caracoles abiertos y cerrados corresponde a un Odu específico con su interpretación. Esta práctica requiere de conocimiento profundo de los Odus, los patakíes (historias sagradas) y la experiencia para interpretar correctamente los mensajes divinos.',
                'keywords' => ['herramienta', 'adivinación', 'caracoles', 'diloggun', 'cowrie', 'odu', 'interpretación']
            ],
            [
                'id' => 'tablero-de-ifa',
                'title' => 'Tablero de Ifá (Opon Ifá)',
                'category' => 'tools',
                'content' => 'El Tablero de Ifá u Opon Ifá es una herramienta sagrada circular utilizada en la adivinación de Ifá. Hecho tradicionalmente de madera, está decorado con símbolos sagrados y la cara de Eshu en la parte superior. Se utiliza junto con los ikines (nueces de palma) o el ekuele (cadena de adivinación) para marcar los Odus durante la consulta. El tablero representa el cosmos y la conexión entre el mundo físico y espiritual.',
                'keywords' => ['herramienta', 'tablero', 'opon', 'ifá', 'adivinación', 'madera', 'símbolos', 'cosmos']
            ],
            
            // Rezos y Cantos
            [
                'id' => 'orin-eleggua',
                'title' => 'Orin para Elegguá',
                'category' => 'prayers',
                'content' => 'Los Orin (cantos) para Elegguá son invocaciones musicales que se utilizan para llamar, saludar y honrar a este Orisha. Estos cantos en lengua yoruba establecen la conexión espiritual y piden su intercesión. Un ejemplo tradicional: "Laroye Elegguá, ago moyugba, abre los caminos, quita los obstáculos, bendice nuestros pasos". Estos cantos se acompañan de ritmos específicos y gestos rituales que amplifican su poder espiritual.',
                'keywords' => ['canto', 'orin', 'elegguá', 'invocación', 'yoruba', 'laroye', 'bendición', 'ritual']
            ],
            
            // Glosario
            [
                'id' => 'ashe',
                'title' => 'Ashé',
                'category' => 'glossary',
                'content' => 'Ashé es el concepto fundamental de poder, energía vital y fuerza dinámica en la religión yoruba. Es la energía que hace posible todo en el universo, el poder que permite que las cosas sucedan. Se considera que todo posee ashé en diferentes grados. Los rituales, oraciones y ofrendas están diseñados para acumular, dirigir y utilizar el ashé. La palabra también se usa como afirmación, similar a "amén", para dar poder y validez a las palabras pronunciadas.',
                'keywords' => ['ashé', 'energía', 'poder', 'vital', 'fuerza', 'universo', 'ritual', 'afirmación']
            ],
            [
                'id' => 'egun',
                'title' => 'Egun',
                'category' => 'glossary',
                'content' => 'Egun se refiere a los espíritus de los ancestros fallecidos en la tradición yoruba. Estos espíritus mantienen una conexión activa con el mundo de los vivos y pueden influir en los asuntos terrenales. Los Egun son venerados y consultados por su sabiduría y protección. Se les hacen ofrendas regulares y se les dedican ceremonias especiales. La relación con los Egun es fundamental para mantener el equilibrio espiritual y la continuidad de las tradiciones familiares.',
                'keywords' => ['egun', 'ancestros', 'espíritus', 'fallecidos', 'veneración', 'sabiduría', 'protección', 'tradición']
            ]
        ];

        $categoryMap = [];
        foreach ($categories as $category) {
            $categoryMap[$category->getId()] = $category;
        }

        $categoryRepository = $this->entityManager->getRepository(KnowledgeCategory::class);
        $allCategories = $categoryRepository->findAll();
        foreach ($allCategories as $cat) {
            $categoryMap[$cat->getId()] = $cat;
        }

        $itemsCreated = 0;
        
        foreach ($knowledgeData as $itemData) {
            $existingItem = $this->entityManager->getRepository(Knowledge::class)->find($itemData['id']);
            
            if (!$existingItem) {
                $item = new Knowledge();
                $item->setId($itemData['id']);
                $item->setTitle($itemData['title']);
                $item->setContent($itemData['content']);
                $item->setKeywords($itemData['keywords']);
                
                if (isset($categoryMap[$itemData['category']])) {
                    $item->setCategory($categoryMap[$itemData['category']]);
                }
                
                $this->entityManager->persist($item);
                $itemsCreated++;
            }
        }

        $this->entityManager->flush();

        // Update category counts
        foreach ($categoryMap as $category) {
            $categoryRepository->updateItemCount($category->getId());
        }

        return $itemsCreated;
    }
} 