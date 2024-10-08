<?php

declare(strict_types=1);

namespace App\Translator;

use Symfony\Component\Translation\MessageCatalogueInterface;
use Symfony\Component\Translation\TranslatorBagInterface;
use Symfony\Contracts\Translation\LocaleAwareInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Disable translations in the test env. We can't deativate the translator service
 * itself as the translator and its service is still used everywhere.
 *
 * @see https://jolicode.com/blog/how-to-properly-manage-translations-in-symfony
 */
final class NoTranslator implements TranslatorInterface, TranslatorBagInterface, LocaleAwareInterface
{
    /**
     * @param TranslatorInterface&TranslatorBagInterface&LocaleAwareInterface $translator
     */
    public function __construct(
        private readonly TranslatorInterface $translator,
    ) {
    }

    /**
     * We don't test parameters for now. My advice would be: don't test parameters
     * until you have to fix a related bug reported or if you use them a lot.
     *
     * @param array<mixed> $parameters
     */
    public function trans(string $id, array $parameters = [], ?string $domain = null, ?string $locale = null): string
    {
        // to find EasyAdmin translations codes, uncomment this
        //        if ($domain === 'EasyAdminBundle') {
        //            dump($id);
        //        }

        return $id;
    }

    public function getCatalogues(): array
    {
        return $this->translator->getCatalogues();
    }

    public function getCatalogue(?string $locale = null): MessageCatalogueInterface
    {
        return $this->translator->getCatalogue($locale);
    }

    public function setLocale(string $locale): void
    {
        $this->translator->setLocale($locale);
    }

    public function getLocale(): string
    {
        return $this->translator->getLocale();
    }
}
