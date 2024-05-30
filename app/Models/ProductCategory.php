<?php

namespace App\Models;

use App\Enums\Status;
use Spatie\MediaLibrary\HasMedia;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Translatable\HasTranslations;
use Staudenmeir\LaravelAdjacencyList\Eloquent\HasRecursiveRelationships;

class ProductCategory extends Model implements HasMedia
{
    use InteractsWithMedia, HasTranslations;
    use HasRecursiveRelationships;

    protected $table = "product_categories";
    protected $fillable = ['name', 'slug', 'description', 'image','status', 'creator_id', 'creator_type', 'editor_id', 'editor_type'];
    protected $hidden = ['creator_type', 'creator_id', 'editor_type', 'updated_at', 'created_at'];
    public $translatable = ['name', 'description'];

//    protected $appends = array('cover');

    public function getThumbAttribute(): string
    {
        if (!empty($this->getFirstMediaUrl('product-category'))) {
            $category = $this->getMedia('product-category')->last();
            return $category->getUrl('thumb');
        }
        return asset('images/default/category/thumb.png');
    }

    public function getCoverAttribute(): string
    {
        if (!empty($this->getFirstMediaUrl('product-category'))) {
            $category = $this->getMedia('product-category')->last();
            return $category->getUrl('cover');
        }
        return asset('images/default/category/cover.png');
    }

    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('thumb')->crop('crop-center', 168, 122)->keepOriginalImageFormat()->sharpen(10);
        $this->addMediaConversion('cover')->crop('crop-center', 640, 960)->keepOriginalImageFormat()->sharpen(10);
    }

    public function products(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Product::class)->where(['status' => Status::ACTIVE]);
    }
}
