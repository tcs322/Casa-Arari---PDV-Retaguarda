<?php

namespace App\Providers;

use App\Models\Fornecedor;
use App\Models\Product;
use App\Models\Produto;
use App\Models\User;
use App\Observers\FornecedorObserver;
use App\Observers\ProductObserver;
use App\Observers\ProdutoObserver;
use App\Observers\UsuarioObserver;
use App\Repositories\Cliente\ClienteEloquentRepository;
use App\Repositories\Cliente\ClienteRepositoryInterface;
use App\Repositories\Fornecedor\FornecedorEloquentRepository;
use App\Repositories\Fornecedor\FornecedorRepositoryInterface;
use App\Repositories\CondicaoPagamento\CondicaoPagamentoEloquentRepository;
use App\Repositories\CondicaoPagamento\CondicaoPagamentoRepositoryInterface;
use App\Repositories\Nota\NotaEloquentRepository;
use App\Repositories\Nota\NotaRepositoryInterface;
use App\Repositories\PlanoPagamento\PlanoPagamentoEloquentRepository;
use App\Repositories\PlanoPagamento\PlanoPagamentoRepositoryInterface;
use App\Repositories\Product\ProductEloquentRepository;
use App\Repositories\Product\ProductRepositoryInterface;
use App\Repositories\Produto\ProdutoEloquentRepository;
use App\Repositories\Produto\ProdutoRepositoryInterface;
use App\Repositories\Usuario\UsuarioEloquentRepository;
use App\Repositories\Usuario\UsuarioRepositoryInterface;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        if ($this->app->environment('local')) {
            $this->app->register(\Laravel\Telescope\TelescopeServiceProvider::class);
            $this->app->register(TelescopeServiceProvider::class);
        }

        $this->app->bind(
            FornecedorRepositoryInterface::class, FornecedorEloquentRepository::class
        );
        $this->app->bind(
            UsuarioRepositoryInterface::class, UsuarioEloquentRepository::class
        );
        $this->app->bind(
            ClienteRepositoryInterface::class, ClienteEloquentRepository::class
        );
        $this->app->bind(
            ProdutoRepositoryInterface::class, ProdutoEloquentRepository::class
        );
        $this->app->bind(
            ProductRepositoryInterface::class, ProductEloquentRepository::class
        );
        $this->app->bind(
            NotaRepositoryInterface::class, NotaEloquentRepository::class
        );
    }


    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Fornecedor::observe(FornecedorObserver::class);
        User::observe(UsuarioObserver::class);
        Produto::observe(ProdutoObserver::class);
        Product::observe(ProductObserver::class);

        \DB::enableQueryLog();
        Validator::extend('validarIdadeAdmissao', function ($attribute, $value, $parameters, $validator) {
            $dataNascimento = $validator->getData()['data_nascimento'];
            $dataAdmissao = $value;

            $diffAnos = now()->parse($dataNascimento)->diffInYears(now()->parse($dataAdmissao));

            return $diffAnos >= 16;
        });

        Validator::extend('notFutureDate', function ($attribute, $value, $parameters, $validator) {
            return now()->gte(now()->parse($value));
        });

        Blade::directive('money', function (string $amount) {
            return 'R$ ' . number_format((float)$amount, 2, '.', ',');
        });
    }
}
