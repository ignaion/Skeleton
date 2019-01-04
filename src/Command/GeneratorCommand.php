<?php

declare(strict_types=1);

namespace KejawenLab\Semart\Skeleton\Command;

use KejawenLab\Semart\Skeleton\Generator\GeneratorFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Muhamad Surya Iksanudin <surya.iksanudin@gmail.com>
 */
class GeneratorCommand extends Command
{
    private $generatorFactory;

    public function __construct(GeneratorFactory $generatorFactory)
    {
        $this->generatorFactory = $generatorFactory;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('semart:crud:generate')
            ->setAliases(['semart:generate', 'semart:gen', 'semart:generate', 'semart:gen'])
            ->setDescription('Generate Simpel CRUD')
            ->setHelp('Generate Simpel CRUD')
            ->addArgument('entity', InputArgument::REQUIRED)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $reflection = new \ReflectionClass($input->getArgument('entity'));

        $this->generatorFactory->generate($reflection);
        $output->writeln(sprintf('<comment>Simple CRUD for %s class is generated</comment>', $reflection->getName()));
    }
}