<?php

namespace App\Http\Controllers;

use App\Models\Catalog\Product;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function __construct(private readonly ProductControllerService $service) {}

    public function __invoke(Product $product): View
    {
        return view('storefront.product', $this->service->getProductPageData($product));
    }
}
