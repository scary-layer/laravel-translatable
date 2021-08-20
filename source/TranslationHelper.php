<?php

namespace ScaryLayer\Translatable;

use Illuminate\Database\Query\Builder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class TranslationHelper
{
    public function __construct(
        private TranslatableInterface $model
    ) {
        //
    }

    /**
     * Get array of all field translations
     */
    public function getArray(string $field): ?array
    {
        $translations = $this->model->translations
            ->where('field', $field)
            ->pluck('value', 'language');

        return $translations->count()
            ? $translations->all()
            : null;
    }

    /**
     * Get array of all translations of all fields
     */
    public function getArrayOfFields(): Collection
    {
        $result = [];
        foreach ($this->model->getTranslatable() as $field) {
            $result[$field] = $this->getArray($field);
        }

        return collect($result);
    }

    /**
     * Create table for model translations
     */
    public function createTable(): void
    {
        Schema::create($this->model->getTranslationsTableName(), function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('model_id');
            $table->string('language', 2);
            $table->string('field');
            $table->text('value')->nullable();
            $table->timestamps();

            $table->unique(['model_id', 'language', 'field']);

            $table->foreign('model_id')
                ->references('id')
                ->on($this->model->getTable())
                ->onDelete('cascade');
            $table->foreign('language')
                ->references('code')
                ->on('languages');
        });
    }

    /**
     * Save translations for given field
     */
    public function save(string $field, ?array $values): bool
    {
        if (!$values || !in_array($field, $this->model->getTranslatable())) {
            return false;
        }

        foreach ($values as $lang => $value) {
            $row = $this->model->translations()->firstOrNew([
                'field' => $field,
                'language' => $lang
            ]);
            $row->value = $value ?? '';
            $row->save();
        }

        return true;
    }

    /**
     * Save translations for present translatable fields
     */
    public function saveMultiple(array $data, ?array $fields = null): void
    {
        $data = collect($data)->only($fields ?? $this->model->getTranslatable());

        foreach ($data as $field => $values) {
            $this->save($field, $values);
        }
    }

    /**
     * Sort by translation relation field
     */
    public function sortBy(Builder $query, string $direction, string $property): Builder
    {
        return $query
            ->leftJoin(
                $this->model->getTranslationsTableName(),
                sprintf('%s.model_id', $this->model->getTranslationsTableName()),
                '=',
                sprintf('%s.id', $this->model->getTable())
            )
            ->where('field', $property)
            ->where('language', app()->getLocale())
            ->select(sprintf('%s.*', $this->model->getTable()))
            ->orderBy(sprintf('%s.value', $this->model->getTranslationsTableName()), $direction);
    }
}
