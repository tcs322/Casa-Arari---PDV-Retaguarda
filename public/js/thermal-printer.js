// thermal-printer.js - Versão para impressão direta GOLDENTEC
console.log("🎯 thermal-printer.js CARREGADO - Modo Goldentec");

class ThermalPrinter {
    constructor() {
        console.log("🔧 ThermalPrinter instanciada - Modo Direto Goldentec");
    }

    async imprimirCupom(dadosCupom) {
        console.log("🖨️ IMPRIMINDO CUPOM (Goldentec):", dadosCupom);

        // Método 1: Tentar impressão direta via Laravel
        const sucesso = await this.imprimirViaLaravel(dadosCupom);

        if (!sucesso) {
            // Método 2: Fallback para impressão browser
            console.log("🌐 Usando impressão browser (fallback)");
            await this.imprimirComBrowser(dadosCupom);
        }
    }

    async imprimirViaLaravel(dadosCupom) {
        try {
            const textoCupom = this.gerarTextoSimples(dadosCupom);

            console.log(
                "📤 Enviando cupom para o Laravel (que redireciona ao servidor local)..."
            );

            const response = await fetch(
                "http://localhost:8050/api/imprimir-direto",
                {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                    },
                    body: JSON.stringify({
                        texto: textoCupom,
                        venda_id: dadosCupom.venda.numero,
                        impressora: "71840",
                    }),
                }
            );

            const resultado = await response.json();

            if (response.ok && resultado.success) {
                console.log(
                    "✅ Impressão enviada com sucesso:",
                    resultado.message
                );
                return true;
            } else {
                console.error(
                    "❌ Erro retornado pela API:",
                    resultado.error || resultado.message
                );
                return false;
            }
        } catch (error) {
            console.error("⚠️ Erro na comunicação com o Laravel:", error);
            return false;
        }
    }

    gerarTextoSimples(dadosCupom) {
        let texto = "";

        // Cabeçalho centralizado
        texto += "     MINHA LIVRARIA LTDA     \n";
        texto += "        CUPOM FISCAL         \n";
        texto += "==============================\n\n";

        // Dados da venda
        texto += `Nº Venda: ${dadosCupom.venda.numero}\n`;
        texto += `Data: ${dadosCupom.venda.data}\n`;
        texto += `Cliente: ${dadosCupom.venda.cliente}\n`;

        if (dadosCupom.venda.cpf_cnpj) {
            texto += `CPF/CNPJ: ${dadosCupom.venda.cpf_cnpj}\n`;
        }

        texto += "------------------------------\n\n";

        // Itens
        texto += "ITENS:\n";
        dadosCupom.itens.forEach((item) => {
            texto += `${item.descricao.substring(0, 28)}\n`;
            texto += `  ${item.quantidade} x R$ ${parseFloat(
                item.valor_unitario
            ).toFixed(2)}\n`;
            texto += `  R$ ${parseFloat(item.valor_total).toFixed(2)}\n\n`;
        });

        texto += "------------------------------\n";

        // Totais
        texto += `Subtotal: R$ ${parseFloat(dadosCupom.totais.subtotal).toFixed(
            2
        )}\n`;

        if (dadosCupom.totais.desconto > 0) {
            texto += `Desconto: R$ ${parseFloat(
                dadosCupom.totais.desconto
            ).toFixed(2)}\n`;
        }

        texto += `TOTAL: R$ ${parseFloat(dadosCupom.totais.total).toFixed(
            2
        )}\n\n`;

        texto += "------------------------------\n";

        // Pagamentos
        texto += "PAGAMENTO:\n";
        dadosCupom.pagamentos.forEach((pagamento) => {
            texto += `${pagamento.forma}: R$ ${parseFloat(
                pagamento.valor
            ).toFixed(2)}\n`;
        });

        // NFC-e
        if (dadosCupom.nfe && dadosCupom.nfe.numero) {
            texto += "\n------------------------------\n";
            texto += `NFC-e Nº: ${dadosCupom.nfe.numero}\n`;
            texto += `Série: ${dadosCupom.nfe.serie}\n`;

            if (
                dadosCupom.nfe.protocolo &&
                dadosCupom.nfe.protocolo !== "N/A"
            ) {
                texto += `Protocolo: ${dadosCupom.nfe.protocolo}\n`;
            }
        }

        // Contingência
        if (dadosCupom.contingencia) {
            texto += "\n*** CONTINGENCIA ***\n";
            texto += "EMITIDO SEM COMUNICACAO SEFAZ\n";
        }

        // Espaço final
        texto += "\n\n\n";
        texto += "     Obrigado pela compra!    \n";
        texto += "==============================\n\n\n\n";

        return texto;
    }

    async imprimirComBrowser(dadosCupom) {
        // ... (mantenha o mesmo código de impressão browser)
    }
}

// Inicialização (mantenha igual)
document.addEventListener("livewire:initialized", function () {
    console.log("🎯 Livewire Inicializado - Modo Goldentec");

    window.thermalPrinter = new ThermalPrinter();

    window.Livewire.on("imprimir-cupom", async function (data) {
        console.log("🎯 EVENTO CAPTURADO - imprimir-cupom", data);

        const printData = {
            venda: {
                numero: data.venda_id,
                data: new Date().toLocaleString(),
                cliente: "Cliente",
                cpf_cnpj: "",
            },
            itens: [
                {
                    descricao: "Produto",
                    quantidade: 1,
                    valor_unitario: "0.00",
                    valor_total: "0.00",
                },
            ],
            totais: {
                subtotal: "0.00",
                desconto: "0.00",
                total: "0.00",
            },
            pagamentos: [
                {
                    forma: "Finalizada",
                    valor: "0.00",
                },
            ],
            contingencia: data.contingencia,
            nfe: {
                numero: data.dados_sefaz?.numero || "N/A",
                serie: data.dados_sefaz?.serie || "N/A",
                protocolo: data.dados_sefaz?.numero_protocolo || "N/A",
            },
        };

        await window.thermalPrinter.imprimirCupom(printData);
        console.log("✅ Processo de impressão finalizado");
    });
});
