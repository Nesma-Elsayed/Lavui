<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class TextBanner extends Model
{
    use HasFactory, HasTranslations;

    protected $fillable = ['name', 'status'];
    protected $hidden = ['updated_at', 'created_at'];
    public $translatable = ['name'];

}
