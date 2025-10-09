<?php

namespace App\Http\Controllers\App\Product;

use App\Actions\Product\ProductAction;
use App\DTO\Product\ProductEditDTO;
use App\DTO\Product\ProductShowDTO;
use App\DTO\Product\ProductStoreDTO;
use App\DTO\Product\ProductUpdateDTO;
use App\Http\Controllers\Controller;
use App\Http\Requests\App\Product\ProductEditRequest;
use App\Http\Requests\App\Product\ProductShowRequest;
use App\Http\Requests\App\Product\ProductStoreRequest;
use App\Http\Requests\App\Product\ProductUpdateRequest;
use App\Services\Product\ProductTributacaoService;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct(
        protected ProductAction $action,
        protected ProductTributacaoService $tributacaoService
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

    public function store(ProductStoreRequest $request)
    {
        // Cria o DTO a partir da request
        $dto = ProductStoreDTO::makeFromRequest($request);
        
        // Aplica tributação automática (retorna um DTO atualizado)
        $dtoComTributacao = $this->tributacaoService->aplicarTributacaoAutomatica($dto);

        // Opcional: Valida a tributação aplicada
        $validacao = $this->tributacaoService->validarTributacao($dtoComTributacao);
        // if (!$validacao['valido']) {
        //     // Log dos erros (não impede a criação, mas registra para análise)
        //     \Log::warning('Produto criado com possíveis inconsistências tributárias: ' . 
        //                  implode(', ', $validacao['erros']));
        // }

        // Envia o DTO para a Action (mantendo a interface existente)
        $product = $this->action->store($dtoComTributacao);

        return redirect()->route('produto.index');
    }

    public function createManyByXml()
    {
        return view('app.product.create-many-by-xml');
    }

    public function edit(string $uuid, ProductEditRequest $request)
    {
        $request->merge([
            "uuid" => $uuid
        ]);

        $formData = $this->action->create();

        $produto = $this->action->edit(ProductEditDTO::makeFromRequest($request));

        return view('app.product.edit', [
            "produto" => $produto,
            "formData" => $formData
        ]);
    }

    public function update(ProductUpdateRequest $request)
    {
        $this->action->update(ProductUpdateDTO::makeFromRequest($request));

        return redirect()->route('produto.index')->with('message', 'Registro atualizado');
    }

    public function show(string $uuid, ProductShowRequest $request)
    {
        $request->merge([
            "uuid" => $uuid
        ]);

        $produto = $this->action->show(ProductShowDTO::makeFromRequest($request));

        return view('app.product.show', ["produto" => $produto]);
    }
}
