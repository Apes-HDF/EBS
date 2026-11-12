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

        // Every step waits for its Meilisearch tasks and throws if one of them failed:
        // the main index is only swapped when the swap index is complete.
        $io->section('Creating indexes if needed...');
        $this->meilisearch->ensureIndex(Meilisearch::PRODUCTS_INDEX);
        $this->meilisearch->ensureIndex(Meilisearch::PRODUCTS_SWAP_INDEX);
        $io->note('  -> DONE');
        $io->newLine();

        $io->section('Resetting swap index and applying settings...');
        $swapIndex = $this->meilisearch->getSwapIndex();
        $this->meilisearch->waitForTasks([
            $swapIndex->deleteAllDocuments()['taskUid'],
            $this->meilisearch->setSettings($swapIndex),
        ]);
        $io->note('  -> DONE');
        $io->newLine();

        $io->section('Indexing products in swap...');
        $count = 0;
        $toIndex = [];
        $taskUids = [];
        // simple trick for code coverage as we don't have 500 products in the fixtures
        $batchSize = $this->environment === 'test' ? 10 : self::BACTH_SIZE;
        $query = $this->productRepository->getIndexable();

        foreach ($query->toIterable() as $product) {
            /** @var Product $product */
            $io->comment(\sprintf('  > adding product %s to batch', $product->getId()));
            $toIndex[] = $product;
            if ((\count($toIndex) % $batchSize) === 0) {
                $taskUids[] = $this->meilisearch->indexProducts($toIndex, $swapIndex);
                $io->note(\sprintf('  > indexing %d product(s) from batch', \count($toIndex)));
                $toIndex = [];
            }
            ++$count;
        }

        $taskUids[] = $this->meilisearch->indexProducts($toIndex, $swapIndex);
        $io->note(\sprintf('  > indexing %d remaining product(s) from batch', \count($toIndex)));

        $this->meilisearch->waitForTasks($taskUids);
        $io->note(\sprintf(' -> %d product(s) indexed.', $count));
        $io->note('  -> DONE');
        $io->newLine();

        $io->section('Swapping indexes...');
        $this->meilisearch->waitForTasks([$this->meilisearch->swapIndexes()]);
        $io->note('  -> DONE');
        $io->newLine();

        $io->section('Resetting swap index...');
        $this->meilisearch->waitForTasks([$swapIndex->deleteAllDocuments()['taskUid']]);
        $io->note('  -> DONE');
        $io->newLine();

        $this->memoryReport($io);
        sleep(1);
        $io->success('DONE');

        return Command::SUCCESS;
    }
}
