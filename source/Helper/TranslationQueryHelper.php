<?php

namespace ScaryLayer\Translatable\Helper;

use Illuminate\Database\Query\Builder;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class TranslationQueryHelper
{
    /**
     * Translation table name
     */
    private string $translationTable;

    /**
     * Create new TranslationQueryHelper instance
     */
    public function __construct(
        private string $table,
        ?string $translationTable = null
    ) {
        $this->translationTable = $translationTable ?? sprintf('%s_translations', $table);
    }

    /**
     * Get table name
     */
    public function getTable(): string
    {
        return $this->table;
    }

    /**
     * Get translation table name
     */
    public function getTranslationTable(): string
    {
        return $this->translationTable;
    }

    /**
     * Create table for model translations
     */
    public function createTable(): void
    {
        Schema::create($this->getTranslationTable(), function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('model_id');
            $table->string('language', 2);
            $table->string('field');
            $table->text('value')->nullable();
            $table->timestamps();

            $table->unique(['model_id', 'language', 'field']);

            $table->foreign('model_id')
                ->references('id')
                ->on($this->getTable())
                ->onDelete('cascade');
            $table->foreign('language')
                ->references('code')
                ->on('languages');
        });
    }

    /**
     * Sort by translation relation field
     */
    public function sortBy(Builder $query, string $direction, string $property): Builder
    {
        return $query
            ->leftJoin(
                $this->getTranslationTable(),
                sprintf('%s.model_id', $this->getTranslationTable()),
                '=',
                sprintf('%s.id', $this->getTable())
            )
            ->where('field', $property)
            ->where('language', app()->getLocale())
            ->select(sprintf('%s.*', $this->getTable()))
            ->orderBy(sprintf('%s.value', $this->getTranslationTable()), $direction);
    }
}
