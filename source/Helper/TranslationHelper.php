<?php

namespace ScaryLayer\Translatable\Helper;

use Illuminate\Support\Collection;
use ScaryLayer\Translatable\Abstraction\AbstractTranslatableModel;

class TranslationHelper
{
    public function __construct(
        private AbstractTranslatableModel $model
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
}
