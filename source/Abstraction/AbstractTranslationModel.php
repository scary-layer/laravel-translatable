<?php

namespace ScaryLayer\Translatable\Abstraction;

use Illuminate\Database\Eloquent\Model;

abstract class AbstractTranslationModel extends Model
{
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = [];
}
