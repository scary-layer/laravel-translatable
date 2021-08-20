<?php

namespace ScaryLayer\Translatable;

use Illuminate\Database\Eloquent\Relations\HasMany;

trait Translatable
{
    /**
     * Get list of model translations
     */
    public function translations(): HasMany
    {
        return $this->hasMany($this->translatableModel, 'model_id');
    }

    /**
     * Accessor for translatable properties
     *
     * @param string $property
     * @return string|null
     */
    public function getAttribute($property)
    {
        $parent = parent::getAttribute($property);

        return !$parent && in_array($property, $this->translatable)
            ? $this->translate($property)
            : $parent;
    }

    /**
     * Get translations table name
     */
    public function getTranslationsTableName(): string
    {
        return (new $this->translatableModel())->getTable();
    }

    /**
     * Get list of translatable model attributes
     */
    public function getTranslatable(): array
    {
        return $this->translatable;
    }

    /**
     * Get translation helper instance
     */
    public function getTranslationHelper(): TranslationHelper
    {
        return new TranslationHelper($this);
    }

    /**
     * Get field value by language
     */
    public function translate(string $field, string $lang = null): ?string
    {
        return $this->translations
            ->where('field', $field)
            ->where('language', $lang ?? app()->getLocale())
            ->first()
            ?->value;
    }
}
