<?php

namespace ScaryLayer\Translatable\Abstraction;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use ScaryLayer\Translatable\Abstraction\AbstractTranslationModel;
use ScaryLayer\Translatable\Helper\TranslationHelper;
use ScaryLayer\Translatable\Helper\TranslationQueryHelper;

abstract class AbstractTranslatableModel extends Model
{
    /**
     * Get list of translatable attributes
     */
    protected static array $translatable;

    /**
     * Get translations model name
     */
    protected static string $translatableModel;

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

    public static function staticGetTable(): string
    {
        return Str::snake(Str::pluralStudly(class_basename(static::class)));
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
    public static function getTranslationModel(): AbstractTranslationModel
    {
        return new static::$translatableModel();
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
    public static function translationQueryHelper(): TranslationQueryHelper
    {
        return new TranslationQueryHelper(
            static::staticGetTable(),
            static::getTranslationModel()->getTable()
        );
    }
}
