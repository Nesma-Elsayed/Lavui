<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Benefit extends Model
{
    use HasFactory, HasTranslations;

    protected $fillable = ['title', 'description', 'icon', 'status', 'sort'];
    protected $hidden = ['creator_type', 'creator_id', 'editor_type', 'editor_id', 'updated_at', 'created_at'];
    public $translatable = ['title', 'description'];
}
