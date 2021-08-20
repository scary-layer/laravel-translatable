<?php

namespace ScaryLayer\Translatable;

use Illuminate\Database\Eloquent\Relations\HasMany;

interface TranslatableInterface
{
    /**
     * Get list of model translations
     */
    public function translations(): HasMany;

    /**
     * Get model table name
     */
    public function getTable();

    /**
     * Get the translatable attributes for the model
     */
    public function getTranslatable(): array;

    /**
     * Get translation helper instance
     */
    public function getTranslationHelper(): TranslationHelper;

    /**
     * Get translations table name
     */
    public function getTranslationsTableName(): string;
}
