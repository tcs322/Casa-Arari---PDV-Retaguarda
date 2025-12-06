<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use App\Models\Venda;
use App\Models\VendaItem;
use App\Models\Product;
use App\Models\User;
use App\Models\Cliente;
use App\Services\NFeGenerateService;

class GerarVendaTesteCommand extends Command
{
    /**
     * O nome e a assinatura do comando.
     *
     * @var string
     */
    protected $signature = 'teste:gerar-venda-nfe';

    /**
     * A descrição do comando.
     *
     * @var string
     */
    protected $description = 'Gera uma venda fictícia com itens e exibe o XML completo da NF-e no terminal.';

    /**
     * Executa o comando.
     */
    public function handle()
    {
        $this->info('🧾 Gerando venda fictícia para teste de NF-e...');

        // Busca ou cria usuário e cliente
        $usuario = \App\Models\User::first() ?? \App\Models\User::factory()->create();
        $cliente = \App\Models\Cliente::first() ?? \App\Models\Cliente::factory()->create();

        // Cria produtos se necessário
        if (Product::count() < 3) {
            Product::factory()->count(3)->create();
        }

        $produtos = Product::take(3)->get();

        // Itens de teste
        $itens = [
            [
                'produto' => $produtos[0],
                'quantidade' => 2,
                'preco' => 10.00,
                'desconto' => 0,
                'tipo_desconto' => 'percentual',
                'subtotal' => 10.00,
            ],
            [
                'produto' => $produtos[1],
                'quantidade' => 1,
                'preco' => 25.00,
                'desconto' => 10, // 10%
                'tipo_desconto' => 'percentual',
                'subtotal' => 22.50,
            ],
            [
                'produto' => $produtos[2],
                'quantidade' => 3,
                'preco' => 5.00,
                'desconto' => 0,
                'tipo_desconto' => 'valor',
                'subtotal' => 5.00,
            ],
        ];

        $valorTotal = collect($itens)->sum(fn ($i) => $i['subtotal'] * $i['quantidade']);

        // Cria venda
        $venda = Venda::create([
            'uuid' => Str::uuid(),
            'usuario_uuid' => $usuario->uuid ?? $usuario->id,
            'cliente_uuid' => $cliente->uuid ?? $cliente->id,
            'forma_pagamento' => 'dinheiro',
            'valor_total' => $valorTotal,
            'valor_recebido' => $valorTotal,
            'troco' => 0,
            'numero_nota_fiscal' => rand(1000, 9999),
            'serie_nfe' => '1',
            'status' => 'finalizada',
            'data_venda' => now(),
        ]);

        // Cria itens
        foreach ($itens as $item) {
            VendaItem::create([
                'uuid' => Str::uuid(),
                'venda_uuid' => $venda->uuid,
                'produto_uuid' => $item['produto']->uuid,
                'quantidade' => $item['quantidade'],
                'preco_unitario' => $item['preco'],
                'preco_total' => $item['preco'] * $item['quantidade'],
                'subtotal' => $item['subtotal'],
                'desconto' => $item['desconto'],
                'tipo_desconto' => $item['tipo_desconto'],
            ]);
        }

        $this->info("✅ Venda de teste criada: {$venda->uuid}");
        $this->info("💰 Valor total: R$ " . number_format($valorTotal, 2, ',', '.'));

        // Gera XML
        $this->line("\n🚀 Gerando XML com NFeGenerateService...");
        $nfeService = new NFeGenerateService();
        $xml = $nfeService->gerarXml($venda);

        // Exibe XML no terminal (formatado)
        $this->newLine(2);
        $this->info("📄 XML GERADO:");
        $this->newLine();

        // Formata XML com indentação para melhor leitura
        $dom = new \DOMDocument();
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = true;
        $dom->loadXML($xml);

        $this->line($dom->saveXML());
        $this->newLine();
        $this->info('✅ Teste concluído com sucesso!');
    }
}
