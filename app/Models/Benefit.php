<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class Benefit extends Model
{
    use HasFactory, HasTranslations, SoftDeletes;

    protected $fillable = ['title', 'description', 'icon', 'status', 'sort', 'creator_id', 'creator_type', 'editor_id', 'editor_type'];
    protected $hidden = ['creator_type', 'creator_id', 'editor_type', 'updated_at', 'created_at'];
    protected array $dates = ['deleted_at'];
    public $translatable = ['title', 'description'];
}
