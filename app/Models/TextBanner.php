<?php

namespace App\Models;

use App\Enums\Status;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class TextBanner extends Model
{
    use HasFactory, HasTranslations, SoftDeletes;

    protected $fillable = ['name', 'status', 'creator_id', 'creator_type', 'editor_id', 'editor_type'];
    protected $hidden = ['creator_type', 'creator_id', 'editor_type', 'updated_at', 'created_at'];
    protected array $dates = ['deleted_at'];
    public $translatable = ['name'];

    public function products(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Product::class)->where(['status' => Status::ACTIVE]);
    }
}
