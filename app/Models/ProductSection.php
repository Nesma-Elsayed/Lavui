<?php

namespace App\Models;

use App\Enums\Status;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Translatable\HasTranslations;

class ProductSection extends Model
{
    use HasFactory, HasTranslations, SoftDeletes;

    protected $table = "product_sections";
    protected $fillable = ['name', 'title','description', 'image','slug', 'status'];
    protected array $dates = ['deleted_at'];
    protected $hidden = ['creator_type', 'creator_id', 'editor_type', 'editor_id', 'updated_at', 'created_at'];
    public $translatable = ['name', 'title','description'];

    public function scopeActive($query, $col = 'status')
    {
        return $query->where($col, Status::ACTIVE);
    }
    public function products(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_section_products', 'product_section_id');
    }

    public function productSectionProducts(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ProductSectionProduct::class, 'product_section_id', 'id');
    }


}
