class BarcodeHandler {
    constructor(livewireComponent, csrfToken) {
        this.livewireComponent = livewireComponent;
        this.csrfToken = csrfToken;
        this.barcodeInput = null;
        this.isReading = false;
        this.barcodeBuffer = "";
        this.timeout = null;

        this.init();
    }

    init() {
        // Criar input hidden para o leitor
        this.createBarcodeInput();

        // Adicionar event listeners
        this.setupEventListeners();

        console.log("Barcode Handler inicializado");
    }

    createBarcodeInput() {
        // Criar input hidden para capturar os códigos de barras
        this.barcodeInput = document.createElement("input");
        this.barcodeInput.type = "text";
        this.barcodeInput.style.cssText = `
            position: absolute;
            left: -9999px;
            opacity: 0;
            width: 1px;
            height: 1px;
        `;
        this.barcodeInput.id = "barcode-scanner-input";
        document.body.appendChild(this.barcodeInput);
    }

    setupEventListeners() {
        // Event listener para o input do leitor
        this.barcodeInput.addEventListener("keydown", (event) => {
            this.handleBarcodeKey(event);
        });

        // Event listener para focar no input do leitor quando clicar em qualquer lugar
        document.addEventListener("click", () => {
            this.barcodeInput.focus();
        });

        // Focar no input automaticamente quando a página carregar
        document.addEventListener("DOMContentLoaded", () => {
            setTimeout(() => {
                this.barcodeInput.focus();
            }, 1000);
        });
    }

    handleBarcodeKey(event) {
        // Ignorar teclas especiais (Shift, Ctrl, Alt, etc)
        if (event.key.length === 1) {
            this.barcodeBuffer += event.key;
        }

        // Limpar timeout anterior
        if (this.timeout) {
            clearTimeout(this.timeout);
        }

        // Configurar novo timeout (assume que o código terminou se não houver input por 100ms)
        this.timeout = setTimeout(() => {
            if (this.barcodeBuffer.length > 3) {
                // Mínimo de caracteres para ser um código válido
                this.processBarcode(this.barcodeBuffer);
            }
            this.barcodeBuffer = "";
        }, 100);

        // Se for Enter, processa imediatamente (para leitores que enviam Enter no final)
        if (event.key === "Enter") {
            event.preventDefault();
            if (this.barcodeBuffer.length > 3) {
                this.processBarcode(this.barcodeBuffer.replace(/\n|\r/g, ""));
            }
            this.barcodeBuffer = "";
            if (this.timeout) {
                clearTimeout(this.timeout);
            }
        }
    }

    async processBarcode(barcode) {
        console.log("Código de barras lido:", barcode);

        try {
            // Opção 1: Usar Livewire diretamente (se disponível)
            if (this.livewireComponent && window.Livewire) {
                this.processWithLivewire(barcode);
            } else {
                // Opção 2: Usar API tradicional
                await this.processWithAPI(barcode);
            }
        } catch (error) {
            console.error("Erro ao processar código de barras:", error);
            this.showError("Erro ao processar código de barras");
        }
    }

    processWithLivewire(barcode) {
        // Usar o método do Livewire para buscar o produto
        if (this.livewireComponent && this.livewireComponent.call) {
            this.livewireComponent.call("buscarPorCodigoBarras", barcode);
        } else {
            // Fallback: preencher o campo de busca
            const searchInput = document.querySelector(
                '[wire\\:model="search"]'
            );
            if (searchInput) {
                searchInput.value = barcode;
                // Disparar evento para o Livewire processar
                searchInput.dispatchEvent(
                    new Event("input", { bubbles: true })
                );
            }
        }
    }

    async processWithAPI(barcode) {
        const response = await fetch("/api/product/barcode-scan", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": this.csrfToken,
                Accept: "application/json",
            },
            body: JSON.stringify({ barcode: barcode }),
        });

        const data = await response.json();

        if (data.success && data.product) {
            this.addProductToCart(data.product);
        } else {
            this.showError("Produto não encontrado: " + barcode);
        }
    }

    addProductToCart(product) {
        // Usar Livewire para adicionar ao carrinho
        if (this.livewireComponent && this.livewireComponent.call) {
            this.livewireComponent.call("adicionarAoCarrinho", product.id);
        } else {
            console.log("Produto encontrado:", product);
            // Aqui você pode adicionar lógica alternativa se necessário
        }
    }

    showError(message) {
        // Mostrar mensagem de erro (pode personalizar conforme seu sistema)
        alert(message);

        // Ou usar um toast notification se disponível
        if (window.showToast) {
            window.showToast(message, "error");
        }
    }

    // Método para ativar/desativar o leitor
    setActive(active) {
        if (active) {
            this.barcodeInput.focus();
        }
        this.isReading = active;
    }
}
