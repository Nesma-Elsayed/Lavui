<?php

namespace App\Http\Resources;


use App\Enums\Ask;
use App\Libraries\AppLibrary;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class SimpleProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        $price = count($this->variations) > 0 ? $this->variation_price : $this->selling_price;
        return [
            'id'                => $this->id,
            'name_en'           => $this->getTranslation('name', 'en'),
            "name_ar"           => $this->getTranslation('name', 'ar'),
            'slug'              => $this->slug,
            'category'          => $this->category?->name,
            'brand'             => $this->brand?->name,
            'image_featured'    => $this->image_featured,
            'currency_price'    => AppLibrary::currencyAmountFormat($price),
            'cover'             => $this->cover,
            'flash_sale'        => $this->add_to_flash_sale == Ask::YES,
            'is_offer'          => Carbon::now()->between($this->offer_start_date, $this->offer_end_date),
            'discounted_price'  => AppLibrary::currencyAmountFormat($price - (($price / 100) * $this->discount)),
            'wishlist'          => (bool)$this->wishlist,
            'size'                => $this->size,
            'arm'                 => $this->arm,
            'bridge'              => $this->bridge,
            'double_price_for_two_lens'  => AppLibrary::flatAmountFormat($this->double_price_for_two_lens),
        ];
    }
}
