<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use Symfony\Component\DomCrawler\Field\ChoiceFormField;
use Symfony\Component\DomCrawler\Form;

trait ChoiceFormFieldTrait
{
    public function tick(Form $form, string $fieldName, bool $tick = true): self
    {
        $field = $form[$fieldName] ?? null;
        if (!$field instanceof ChoiceFormField) {
            throw new \InvalidArgumentException('Invalid choice field.');
        }

        if ($tick) {
            $field->tick();
        } else {
            $field->untick();
        }

        return $this;
    }
}
