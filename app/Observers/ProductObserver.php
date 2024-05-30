<?php

namespace App\Observers;

use App\Models\Product;
use App\Models\ProductTag;

class ProductObserver
{

    /**
     * Handle the Product "created" event.
     */
    public function created(Product $product): void
    {
        if (request('tags')) {
//            $tagItems = json_decode(request('tags'), true);
//            foreach ($tagItems as $tagItem) {
                ProductTag::create([
                    'product_id' => $product->id,
                    'name'       => request('tags')
                ]);
//            }
        }
    }

    /**
     * Handle the Product "updated" event.
     */
    public function updated(Product $product): void
    {
        //
    }

    /**
     * Handle the Product "deleted" event.
     */
    public function deleted(Product $product): void
    {
        //
    }

    /**
     * Handle the Product "restored" event.
     */
    public function restored(Product $product): void
    {
        //
    }

    /**
     * Handle the Product "force deleted" event.
     */
    public function forceDeleted(Product $product): void
    {
        //
    }
}
