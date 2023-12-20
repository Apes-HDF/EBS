<?php

declare(strict_types=1);

namespace App\Helper;

use App\Controller\Admin\DashboardController;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FieldDto;
use Goodby\CSV\Export\Standard\Exporter;
use Goodby\CSV\Export\Standard\ExporterConfig;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Adapted from the Symfony cast class. This one also handles translation.
 *
 * @see https://symfonycasts.com/screencast/easyadminbundle/global-action
 */
final class CsvExporter
{
    public function __construct(
        public readonly TranslatorInterface $translator,
        public readonly StringHelper $stringHelper,
    ) {
    }

    public function createResponseFromQueryBuilder(QueryBuilder $queryBuilder, FieldCollection $fields, string $filename): Response
    {
        $result = $queryBuilder->getQuery()->getArrayResult();
        $fieldNames = array_values(array_map(static fn (FieldDto $dto) => $dto->getProperty(), iterator_to_array($fields->getIterator())));

        $data = [];
        foreach ($result as $index => $row) {
            /** @var array<mixed> $row */
            foreach ($row as $columnKey => $columnValue) {
                // only allow fields on list

                if (!\in_array($columnKey, $fieldNames, true)) {
                    continue;
                }

                if ($columnValue instanceof \DateTimeInterface) {
                    $columnValue = $columnValue->format('Y-m-d H:i:s'); // @todo use a parameter
                }

                if ($columnValue instanceof \UnitEnum) {
                    $columnValue = $this->translator->trans($columnValue->name, [], DashboardController::DOMAIN);
                }

                /*
                if ($columnValue instanceof AbstractUid) {
                    $columnValue = (string) $columnValue;
                }

                if (\is_array($columnValue)) {
                    $columnValue = implode(' ,', $columnValue);
                }
                */

                if (\is_bool($columnValue)) {
                    $columnValue = $this->translator->trans($columnValue ? 'yes' : 'no', [], DashboardController::DOMAIN);
                }

                $data[$index][$columnKey] = $columnValue;
            }
        }

        // Preserve the fields' order
        $orderedFieldNames = array_keys($data[0] ?? []);

        // Humanize headers based on translations (same as EA would do)
        $headers = [];
        foreach ($orderedFieldNames as $property) {
            $headers[$property] = $this->translator->trans($this->stringHelper->humanize($property), [], DashboardController::DOMAIN);
        }
        array_unshift($data, $headers);

        $response = new StreamedResponse(function () use ($data) {
            $config = new ExporterConfig();
            $exporter = new Exporter($config);
            $exporter->export('php://output', $data);
        });
        $dispositionHeader = $response->headers->makeDisposition(
            HeaderUtils::DISPOSITION_ATTACHMENT,
            $filename
        );
        $response->headers->set('Content-Disposition', $dispositionHeader);
        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');

        return $response;
    }
}
