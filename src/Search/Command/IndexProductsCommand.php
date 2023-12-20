<?php

declare(strict_types=1);

namespace App\Search\Command;

use App\Command\CommandTrait;
use App\Entity\Product;
use App\Repository\ProductRepository;
use App\Search\Meilisearch;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Serializer\Exception\ExceptionInterface;

/**
 * @see IndexProductsCommandTest
 */
#[AsCommand(
    name: self::CMD,
    description: self::DESCRIPTION,
)]
final class IndexProductsCommand extends Command
{
    use CommandTrait;

    public const CMD = 'app:index-products';
    public const DESCRIPTION = 'Index all products in Meilisearch.';

    private const BACTH_SIZE = 500; // recommanded value

    public function __construct(
        private readonly ProductRepository $productRepository,
        private readonly Meilisearch $meilisearch,
        #[Autowire('%kernel.environment%')]
        private readonly string $environment,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->configureCommand(self::DESCRIPTION);
    }

    /**
     * @throws ExceptionInterface
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title(self::DESCRIPTION.' ('.$this->environment.' env)');
        $this->memoryReport($io);

        $io->section('Resetting swap index...');
        $swapIndex = $this->meilisearch->getSwapIndex();
        $swapIndex->deleteAllDocuments();
        $io->note('  -> DONE');
        $io->newLine();

        $io->section('Indexing products in swap...');
        $count = 0;
        $toIndex = [];
        // simple trick for code coverage as we don't have 500 products in the fixtures
        $batchSize = $this->environment === 'test' ? 10 : self::BACTH_SIZE;
        $query = $this->productRepository->getIndexable();

        foreach ($query->toIterable() as $product) {
            /** @var Product $product */
            $io->comment(sprintf('  > adding product %s to batch', $product->getId()));
            $toIndex[] = $product;
            if ((\count($toIndex) % $batchSize) === 0) {
                $this->meilisearch->indexProducts($toIndex, $swapIndex);
                $io->note(sprintf('  > indexing %d product(s) from batch', \count($toIndex)));
                $toIndex = [];
            }
            ++$count;
        }

        $this->meilisearch->indexProducts($toIndex, $swapIndex);
        $io->note(sprintf('  > indexing %d remaining product(s) from batch', \count($toIndex)));

        $io->note(sprintf(' -> %d product(s) indexed.', $count));
        $io->note('  -> DONE');
        $io->newLine();

        $io->section('Swapping indexes...');
        $this->meilisearch->swapIndexes();
        $io->note('  -> DONE');
        $io->newLine();

        $io->section('Applying settings...');
        $this->meilisearch->setSettings();
        $io->note('  -> DONE');

        $io->section('Resetting swap index...');
        $swapIndex = $this->meilisearch->getSwapIndex();
        $swapIndex->deleteAllDocuments();
        $io->note('  -> DONE');
        $io->newLine();

        $this->memoryReport($io);
        sleep(1);
        $io->success('DONE');

        return Command::SUCCESS;
    }
}
