<?php

declare(strict_types=1);

namespace App\Message\Command\Admin;

use function Symfony\Component\String\u;

/**
 * Abstract class for all the form command DTO. Form DTO are split into sections.
 */
abstract class AbstractFormCommand
{
    /**
     * Get all the available sections of the form.
     *
     * @return array<string>
     */
    abstract protected function getSections(): array;

    /**
     * Convert the DTO so it can be stored in the database.
     *
     * @todo Should be tranform ?
     *
     * @return array<array<string, mixed>>
     */
    public function toJsonArray(): array
    {
        foreach (array_keys(get_class_vars($this::class)) as $classVar) {
            $array[$this->getSection($classVar)][$classVar] = $this->{$classVar}; // @phpstan-ignore-line
        }

        return $array ?? [];
    }

    /**
     * Extract the section from a property name.
     */
    protected function getSection(string $classVar): string
    {
        foreach ($this->getSections() as $section) {
            if (u($classVar)->startsWith($section)) {
                return $section;
            }
        }

        throw new \UnexpectedValueException(\sprintf('Invalid property name, it should start with "%s"', implode(', ', $this->getSections())));
    }
}
