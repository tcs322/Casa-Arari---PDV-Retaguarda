<?php

namespace App\Livewire\Components\App;

use App\Enums\SituacaoUsuarioEnum;
use App\Models\Product;
use App\Models\Cliente;
use App\Models\User;
use Livewire\Component;

class FrenteCaixa extends Component
{
    public $search = '';
    public $searchCliente = '';
    public $searchUsuario = '';
    public $produtosEncontrados = [];
    public $clientesEncontrados = [];
    public $usuariosEncontrados = [];
    public $clienteSelecionado = null;
    public $usuarioSelecionado = null;
    public $carrinho = [];
    public $totalCarrinho = 0;
    public $descontoGeral = 0;
    public $tipoDescontoGeral = 'percentual';
    public $descontoCalculado = 0;
    
    public function buscarProdutos()
    {
        if (strlen($this->search) < 2) {
            $this->produtosEncontrados = [];
            return;
        }

        $this->produtosEncontrados = Product::where(function ($query) {
                $query->where('nome_titulo', 'like', '%' . $this->search . '%')
                    ->orWhere('codigo', 'like', '%' . $this->search . '%');
            })
            ->where('estoque', '>', 0) // 🧠 garante que só produtos com estoque positivo apareçam
            ->limit(10)
            ->get()
            ->toArray();
    }

    public function buscarPorCodigoBarras($barcode)
    {
        // Limpar espaços e caracteres especiais
        $barcode = trim($barcode);
        
        if (empty($barcode)) {
            return;
        }

        // Buscar produto pelo código de barras
        $produto = Product::where('codigo_barras', $barcode)
            ->orWhere('codigo', $barcode) // Também busca pelo código interno
            ->first();

        if ($produto) {
            // Se encontrou, adiciona ao carrinho
            $this->adicionarAoCarrinho($produto->id);
            
            // Opcional: mostrar mensagem de sucesso
            session()->flash('message', 'Produto "' . $produto->nome_titulo . '" adicionado via código de barras!');
        } else {
            // Se não encontrou, preenche o campo de busca para busca normal
            $this->search = $barcode;
            $this->buscarProdutos();
            
            // Opcional: mostrar mensagem
            session()->flash('error', 'Produto com código "' . $barcode . '" não encontrado. Use a busca manual.');
        }
        
        // Disparar evento para re-focar no leitor
        $this->dispatchBrowserEvent('barcode-processed');
    }

    public function buscarClientes()
    {
        if (strlen($this->searchCliente) < 2) {
            $this->clientesEncontrados = [];
            return;
        }

        $this->clientesEncontrados = Cliente::where('nome', 'like', '%' . $this->searchCliente . '%')
            ->orWhere('cpf', 'like', '%' . $this->searchCliente . '%')
            ->limit(10)
            ->get()
            ->map(function ($cliente) {
                return [
                    'uuid' => $cliente->uuid,
                    'nome' => $cliente->nome,
                    'cpf' => $cliente->cpf,
                    'total_vendas' => $cliente->contarVendas()
                ];
            })
            ->toArray();
    }

    public function selecionarCliente($clienteData)
    {
        $this->clienteSelecionado = $clienteData;
        $this->searchCliente = '';
        $this->clientesEncontrados = [];
    }

    public function removerCliente()
    {
        $this->clienteSelecionado = null;
        $this->searchCliente = '';
    }

    public function buscarUsuarios()
    {
        if (strlen($this->searchUsuario) < 2) {
            $this->usuariosEncontrados = [];
            return;
        }

        $this->usuariosEncontrados = User::where('name', 'like', '%' . $this->searchUsuario . '%')
            ->where('situacao', SituacaoUsuarioEnum::ATIVO())
            ->limit(10)
            ->get()
            ->map(function ($usuario) {
                return [
                    'uuid' => $usuario->uuid,
                    'name' => $usuario->name,
                ];
            })
            ->toArray();
    }

    public function selecionarUsuario($usuarioData)
    {
        $this->usuarioSelecionado = $usuarioData;
        $this->searchUsuario = '';
        $this->usuariosEncontrados = [];
    }

    public function removerUsuario()
    {
        $this->usuarioSelecionado = null;
        $this->searchUsuario = '';
    }

    public function adicionarAoCarrinho($produtoId)
    {
        $produto = Product::find($produtoId);
        
        if (!$produto) {
            return;
        }

        $index = array_search($produtoId, array_column($this->carrinho, 'id'));
        
        if ($index !== false) {
            $this->carrinho[$index]['quantidade']++;
        } else {
            $this->carrinho[] = [
                'id' => $produto->id,
                'uuid' => $produto->uuid,
                'codigo' => $produto->codigo,
                'nome' => $produto->nome_titulo,
                'preco' => $produto->preco_venda,
                'quantidade' => 1,
                'subtotal' => $produto->preco_venda,
                'desconto' => $this->descontoGeral,
                'tipo_desconto' => $this->tipoDescontoGeral
            ];
        }

        $this->calcularTotal();
        $this->search = '';
        $this->produtosEncontrados = [];
    }

    public function atualizarQuantidade($index, $quantidade)
    {
        if ($quantidade < 1) {
            unset($this->carrinho[$index]);
            $this->carrinho = array_values($this->carrinho);
        } else {
            $this->carrinho[$index]['quantidade'] = $quantidade;
            $this->carrinho[$index]['subtotal'] = $this->carrinho[$index]['preco'] * $quantidade;
        }

        $this->calcularTotal();
    }

    public function aplicarDescontoIndividual($index, $desconto, $tipo = 'percentual')
    {
        if (!isset($this->carrinho[$index])) {
            return;
        }

        $this->carrinho[$index]['desconto'] = floatval($desconto);
        $this->carrinho[$index]['tipo_desconto'] = $tipo;
        $this->calcularTotal();
    }

    public function aplicarDescontoGeral()
    {
        $this->calcularTotal();
    }

    public function removerDoCarrinho($index)
    {
        unset($this->carrinho[$index]);
        $this->carrinho = array_values($this->carrinho);
        $this->calcularTotal();
    }

    public function limparCarrinho()
    {
        $this->carrinho = [];
        $this->totalCarrinho = 0;
        $this->descontoGeral = 0;
        $this->tipoDescontoGeral = 'percentual';
        
        session()->forget('venda_dados');
        session()->forget('carrinho');
        
        session()->flash('message', 'Carrinho limpo com sucesso!');
        $this->dispatch('carrinho-limpo');
    }

    protected function calcularTotal()
    {
        $total = 0;

        // 1️⃣ Calcula subtotais individuais com descontos próprios
        foreach ($this->carrinho as $index => $item) {
            $subtotal = $item['preco'] * $item['quantidade'];
            
            if ($item['desconto'] > 0) {
                if ($item['tipo_desconto'] === 'percentual') {
                    $subtotal -= $subtotal * ($item['desconto'] / 100);
                } else {
                    $subtotal -= $item['desconto'];
                }
            }
    
            $this->carrinho[$index]['subtotal'] = max($subtotal, 0);
            $total += $this->carrinho[$index]['subtotal'];
        }
    
        // 2️⃣ Calcula e aplica desconto geral
        $this->descontoCalculado = 0; // inicializa para evitar lixo de memória
        
        if ($this->descontoGeral > 0 && $total > 0) {
            if ($this->tipoDescontoGeral === 'percentual') {
                // Calcula o desconto real com base no total bruto
                $this->descontoCalculado = round($total * ($this->descontoGeral / 100), 2);
                
                // Aplica proporcionalmente o desconto percentual
                foreach ($this->carrinho as $index => $item) {
                    $novoSubtotal = $item['subtotal'] - ($item['subtotal'] * ($this->descontoGeral / 100));
                    $this->carrinho[$index]['subtotal'] = round(max($novoSubtotal, 0), 2);
                }
    
                $total -= $this->descontoCalculado;
    
            } else {
                // 🔹 Valor fixo
                $this->descontoCalculado = round(min($this->descontoGeral, $total), 2);
    
                $descontoTotalAplicado = 0;
                $ultimoIndex = array_key_last($this->carrinho);
    
                foreach ($this->carrinho as $index => $item) {
                    $proporcao = $item['subtotal'] / $total;
                    $descontoProporcional = $this->descontoCalculado * $proporcao;
    
                    if ($index === $ultimoIndex) {
                        $descontoProporcional = $this->descontoCalculado - $descontoTotalAplicado;
                    }
    
                    $descontoProporcional = round($descontoProporcional, 2);
                    $descontoTotalAplicado += $descontoProporcional;
    
                    $novoSubtotal = $item['subtotal'] - $descontoProporcional;
                    $this->carrinho[$index]['subtotal'] = round(max($novoSubtotal, 0), 2);
                }
    
                $total -= $this->descontoCalculado;
            }
        }
    
        // 3️⃣ Total final
        $this->totalCarrinho = round(max($total, 0), 2);
    }
    
    public function finalizarVenda()
    {
        if (empty($this->carrinho)) {
            session()->flash('error', 'Adicione produtos ao carrinho primeiro!');
            return;
        }

        // Valida se há cliente selecionado
        if (!$this->clienteSelecionado) {
            session()->flash('error', 'Selecione um cliente antes de finalizar a venda!');
            return;
        }

        // Salva os dados da venda na sessão para passar para a próxima página
        session()->put('venda_dados', [
            'carrinho' => $this->carrinho,
            'total' => $this->totalCarrinho,
            'desconto_geral' => $this->descontoGeral,
            'tipo_desconto_geral' => $this->tipoDescontoGeral,
            'cliente' => $this->clienteSelecionado, // Agora obrigatório
            'usuario' => $this->usuarioSelecionado
        ]);

        // Redireciona para a página de pagamento
        return redirect()->route('frente-caixa.pagamento');
    }

    public function render()
    {
        return view('livewire.components.app.frente-caixa');
    }
}