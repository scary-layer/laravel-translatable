<?php

namespace ScaryLayer\Translatable\Trait;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use ScaryLayer\Translatable\Abstraction\AbstractTranslationModel;
use ScaryLayer\Translatable\Helper\TranslationHelper;
use ScaryLayer\Translatable\Helper\TranslationQueryHelper;

abstract class AbstractTranslatableModel extends Model
{
    /**
     * Get list of translatable attributes
     */
    protected array $translatable;

    /**
     * Get translations model name
     */
    protected string $translatableModel;

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
     * Get list of translatable model attributes
     */
    public function getTranslatable(): array
    {
        return $this->translatable;
    }

    /**
     * Get new translation model instance
     */
    public function getTranslationModel(): AbstractTranslationModel
    {
        return new $this->translatableModel();
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

    /**
     * Get translation helper instance
     */
    public function translationHelper(): TranslationHelper
    {
        return new TranslationHelper($this);
    }

    /**
     * Get translation query helper instance
     */
    public function translationQueryHelper(): TranslationQueryHelper
    {
        return new TranslationQueryHelper($this->getTable(), $this->getTranslationModel()->getTable());
    }
}
