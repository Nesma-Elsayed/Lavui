<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Slider extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $table = "sliders";
    protected $fillable = ['title', 'link', 'description', 'status'];
    protected $casts = [
        'id'          => 'integer',
        'title'       => 'string',
        'description' => 'string',
        'status'      => 'integer',
        'link'        => 'string',
    ];

//    public function getImageAttribute(): string
//    {
//        if (!empty($this->getFirstMediaUrl('slider'))) {
//            $slider = $this->getMedia('slider')->last();
//            return $slider->getUrl('cover');
//        }
//        return asset('images/default/slider.png');
//    }

    public function getEnImages()
    {
//        return $this->getMedia('slider')->map(function (Media $media) {
//            return [
//                'url' => $media->getUrl('cover'),
//                'language' => $media->getCustomProperty('en'),
//            ];
//        })->toArray();
        $language = app()->getLocale(); // Get the current language from the application locale
        $sliderImage = $this->getMedia('slider')
            ->filter(fn($mediaItem) => $mediaItem->getCustomProperty('language') === $language)
            ->last();

        return $sliderImage ? $sliderImage->getUrl('cover') : asset('images/default/slider.png');
    }
    public function getArImages()
    {
//        return $this->getMedia('slider')->map(function (Media $media) {
//            return [
//                'url' => $media->getUrl('cover'),
//                'language' => $media->getCustomProperty('en'),
//            ];
//        })->toArray();
        $language = app()->getLocale(); // Get the current language from the application locale
        $sliderImage = $this->getMedia('slider')
            ->filter(fn($mediaItem) => $mediaItem->getCustomProperty('language') === 'ar')
            ->last();

        return $sliderImage ? $sliderImage->getUrl('cover') : asset('images/default/slider.png');
    }

    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('cover')->crop('crop-center', 1126, 400)->keepOriginalImageFormat()->sharpen(10);
    }
}
