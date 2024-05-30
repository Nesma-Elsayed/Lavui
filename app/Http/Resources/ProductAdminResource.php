<?php

namespace App\Http\Resources;



use App\Enums\Ask;
use Carbon\Carbon;
use App\Libraries\AppLibrary;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductAdminResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request): array
    {
        $price = count($this->variations) > 0 ? $this->variation_price : $this->selling_price;
        return [
            "id"                         => $this->id,
            "name_en"                    => $this->getTranslation('name', 'en'),
            "name_ar"                    => $this->getTranslation('name', 'ar'),
            "sku"                        => $this->sku,
            "slug"                       => $this->slug,
            "product_category_id"        => $this->product_category_id,
            "barcode_id"                 => $this->barcode_id,
            "product_brand_id"           => $this->product_brand_id,
            "group_id"                   => $this->group_id,
            "text_banner_id"             => $this->text_banner_id,
            "unit_id"                    => $this->unit_id,
            "tax_id"                     => ProductTaxResource::collection($this->taxes),
            "flat_buying_price"          => AppLibrary::flatAmountFormat($this->buying_price),
            "flat_selling_price"         => AppLibrary::flatAmountFormat($this->selling_price),
            "status"                     => $this->status,
            "can_purchasable"            => $this->can_purchasable,
            "show_stock_out"             => $this->show_stock_out,
            "maximum_purchase_quantity"  => $this->maximum_purchase_quantity,
            "low_stock_quantity_warning" => $this->low_stock_quantity_warning,
            "refundable"                 => $this->refundable,
            "description_en"             => $this->description === null ? '' : $this->getTranslation('description', 'en'),
            "description_ar"             => $this->description === null ? '' : $this->getTranslation('description', 'ar'),
            "shipping_and_return"        => $this->shipping_and_return === null ? '' : $this->shipping_and_return,
            "product_tags"               => ProductTagResource::collection($this->tags),
            "category_name"              => $this?->category?->name,
            "order"                      => abs($this?->productOrders->sum('quantity')),
            'currency_price'             => AppLibrary::currencyAmountFormat($price),
            "cover"                      => $this->cover,
            'flash_sale'                 => $this->add_to_flash_sale == Ask::YES,
            'is_offer'                   => Carbon::now()->between($this->offer_start_date, $this->offer_end_date),
            'discounted_price'           => AppLibrary::currencyAmountFormat($price - (($price / 100) * $this->discount)),
//            'rating_star'                => $this->rating_star,
//            'rating_star_count'          => $this->rating_star_count,
            'image_featured'             => $this->image_featured,
            'size'                       => $this->size,
            'arm'                        => $this->arm,
            'bridge'                     => $this->bridge,
            'double_price_for_two_lens'  => AppLibrary::flatAmountFormat($this->double_price_for_two_lens),
        ];
    }
}
