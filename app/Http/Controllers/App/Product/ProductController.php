<?php

namespace App\Http\Controllers\App\Product;

use App\Actions\Product\ProductAction;
use App\DTO\Product\ProductStoreDTO;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct(
        protected ProductAction $action
    ) {}

    public function create()
    {   
        $formData = $this->action->create();
        
        return view('app.product.create', compact('formData'));
    }

    public function index(Request $request)
    {
        $products = $this->action->paginate(
            page: $request->get('page', 1),
            totalPerPage: $request->get('per_page', 6),
            filter: $request->get('filter'),
        );

        $filters = ['filter' => $request->get('filter', '')];
        
        return view('app.product.index', compact('products', 'filters'));
    }

    public function store(Request $request)
    {
        $this->action->store(ProductStoreDTO::makeFromRequest($request));

        return redirect()->route('produto.index');
    }

    public function createManyByXml()
    {
        return view('app.product.create-many-by-xml');
    }
}
