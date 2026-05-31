<?php

declare(strict_types=1);

namespace App\Service;

use Symfony\Component\Form\FormInterface;

final readonly class BoardDetailFormErrors
{
    private function __construct(
        public ?FormInterface $rowCreateForm = null,
        public ?FormInterface $rowUpdateForm = null,
        public ?int $rowUpdateId = null,
        public ?FormInterface $taskCreateForm = null,
        public ?int $taskCreateRowId = null,
        public ?FormInterface $taskUpdateForm = null,
        public ?int $taskUpdateId = null,
        public ?FormInterface $taskShootForm = null,
        public ?int $taskShootId = null,
    ) {
    }

    public static function none(): self
    {
        return new self();
    }

    public static function rowCreate(FormInterface $form): self
    {
        return new self(rowCreateForm: $form);
    }

    public static function rowUpdate(int $rowId, FormInterface $form): self
    {
        return new self(rowUpdateForm: $form, rowUpdateId: $rowId);
    }

    public static function taskCreate(int $rowId, FormInterface $form): self
    {
        return new self(taskCreateForm: $form, taskCreateRowId: $rowId);
    }

    public static function taskUpdate(int $taskId, FormInterface $form): self
    {
        return new self(taskUpdateForm: $form, taskUpdateId: $taskId);
    }

    public static function taskShoot(int $taskId, FormInterface $form): self
    {
        return new self(taskShootForm: $form, taskShootId: $taskId);
    }
}
