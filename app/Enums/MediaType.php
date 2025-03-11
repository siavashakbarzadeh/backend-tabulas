<?php

namespace App\Enums;

use App\Services\Media\DefaultMediaHandler;

enum MediaType: string
{
    case DEFAULT = 'default';
    case IMAGE = "image";

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return bool
     */
    public function isDefault(): bool
    {
        return $this->getName() == 'default';
    }

    /**
     * @return array|null
     */
    public function getMimeTypes(): array|null
    {
        return $this->isDefault() ? [] : config("media.{$this->getValue()}.mime_types");
    }

    /**
     * @return string|null
     */
    public function getHandler(): string|null
    {
        return $this->isDefault() ? DefaultMediaHandler::class : config("media.{$this->getValue()}.handler");
    }

}
