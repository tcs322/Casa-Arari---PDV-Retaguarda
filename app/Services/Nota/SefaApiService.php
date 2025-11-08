<?php
// app/Services/Nota/SefazApiService.php

namespace App\Services\Nota;

use NFePHP\NFe\Tools;
use NFePHP\Common\Certificate;
use Exception;
use Illuminate\Support\Facades\Log;

class SefaApiService
{
    private $tools;
    private $config;

    public function __construct()
    {
        $this->config = $this->getConfig();
        $this->tools = new Tools(json_encode($this->config), $this->getCertificate());
    }

    /**
     * Envia NF-e para autorização (Síncrono)
     */
    public function autorizarNFe(string $xmlAssinado): array
    {
        try {
            $idLote = $this->gerarIdLoteValido();
            Log::info("📦 Transmitindo NF-e. Lote: {$idLote}");

            // Envia o lote para a SEFAZ
            $response = $this->tools->sefazEnviaLote([$xmlAssinado], $idLote, 1);

            // Se a SEFAZ respondeu com sucesso, processa normalmente
            $resultado = $this->processarRespostaAutorizacao($response, $idLote);

            // 🚀 Retorno padrão de sucesso
            return [
                'success' => true,
                'tipo' => 'autorizada',
                'chave_acesso' => $resultado['chave_acesso'] ?? null,
                'numero_protocolo' => $resultado['numero_protocolo'] ?? null,
                'xml' => $resultado['xml'] ?? null,
                'mensagem' => 'NF-e autorizada com sucesso'
            ];

        } catch (\Exception $e) {
            $mensagem = $e->getMessage();

            // 🧭 Palavras-chave para detectar erro de comunicação / SEFAZ fora do ar
            $indicadoresContingencia = [
                'Could not connect', 'Connection refused', 'SSL',
                'timeout', 'Could not resolve host',
                'SEFAZ INDISPONÍVEL', 'Falha de conexão', 'SOAP'
            ];

            foreach ($indicadoresContingencia as $palavra) {
                if (stripos($mensagem, $palavra) !== false) {
                    Log::warning("⚙️ Entrando em contingência automática: {$mensagem}");

                    return [
                        'success' => false,
                        'tipo' => 'contingencia',
                        'erro' => 'Falha de comunicação com a SEFAZ',
                        'mensagem' => 'Servidor SEFAZ indisponível - emitido em contingência',
                        'codigo_erro' => 'CONTINGENCIA'
                    ];
                }
            }

            // ❌ Caso contrário, é uma rejeição ou erro de retorno SEFAZ
            Log::error("❌ Erro ao autorizar NF-e: {$mensagem}");

            return [
                'success' => false,
                'tipo' => 'rejeitada',
                'erro' => 'SEFAZ: ' . $mensagem,
                'codigo_erro' => $this->extrairCodigoErro($mensagem),
                'mensagem' => 'NF-e rejeitada pela SEFAZ'
            ];
        }
    }

    /**
     * Gera ID de lote válido (15 dígitos numéricos)
     */
    private function gerarIdLoteValido(): string
    {
        // Opção 1: Timestamp compacto (recomendado)
        $timestamp = date('ymdHis'); // 12 dígitos: YYMMDDHHMMSS
        $random = str_pad(mt_rand(0, 999), 3, '0', STR_PAD_LEFT); // 3 dígitos
        return $timestamp . $random; // Total: 15 dígitos
        
        // Opção 2: Número sequencial simples
        // return str_pad(mt_rand(1, 999999999999999), 15, '0', STR_PAD_LEFT);
    }

    /**
     * Consulta situação da NF-e
     */
    public function consultarSituacao(string $chaveAcesso): array
    {
        try {
            $response = $this->tools->sefazConsultaChave($chaveAcesso);
            return $this->processarRespostaConsulta($response);
        } catch (Exception $e) {
            return [
                'success' => false,
                'erro' => 'Erro na consulta: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Consulta status do serviço SEFAZ
     */
    public function consultarStatusServico(): array
    {
        try {
            $response = $this->tools->sefazStatus();
            return $this->processarRespostaStatus($response);
        } catch (Exception $e) {
            return [
                'success' => false,
                'erro' => 'Serviço SEFA indisponível: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Processa resposta da autorização
     */
    private function processarRespostaAutorizacao(string $response, string $idLote): array
    {
        try {
            Log::info("📨 INICIANDO processarRespostaAutorizacao");
        
            // ✅ SALVAR RESPOSTA COMPLETA PARA ANÁLISE
            file_put_contents(storage_path('logs/resposta_sefaz_completa.xml'), $response);
            Log::info("💾 Resposta completa salva em: storage/logs/resposta_sefaz_completa.xml");
            
            // ✅ LOG DA RESPOSTA COMPLETA
            Log::info("📄 RESPOSTA COMPLETA SEFAZ (primeiros 1000 chars):\n" . substr($response, 0, 1000));
            
            // Limpar resposta (remover possíveis caracteres inválidos)
            $response = trim($response);
            
            // Verificar se a resposta está vazia
            if (empty($response)) {
                throw new Exception('Resposta da SEFAZ está vazia');
            }
            
            // Verificar se começa com XML
            if (strpos($response, '<?xml') !== 0 && strpos($response, '<soap:Envelope') !== 0) {
                Log::warning("Resposta não inicia com XML: " . substr($response, 0, 100));
                // Mas ainda tentar processar
            }
            
            // Tentar carregar o XML
            $xml = simplexml_load_string($response);
            
            if ($xml === false) {
                // Tentar extrair XML de dentro de SOAP
                $xml = $this->extrairXmlDoSoap($response, $idLote);
                if ($xml === false) {
                    throw new Exception('Resposta não é um XML válido. Conteúdo: ' . substr($response, 0, 500));
                }
            }
            
            // ... resto do código de processamento igual ao anterior
            
            $resultado = $this->processarEstruturaResposta($xml, $idLote);
        
            Log::info("📋 RESULTADO FINAL processarRespostaAutorizacao:", $resultado);
            
            return $resultado;
                
        } catch (Exception $e) {
            Log::error('Erro ao processar resposta: ' . $e->getMessage());
            return [
                'success' => false,
                'erro' => 'Erro no processamento da resposta: ' . $e->getMessage(),
                'codigo_erro' => 'PROCESSAMENTO' // ← CORREÇÃO: Adicionar codigo_erro
            ];
        }
    }

    private function extrairXmlDoSoap(string $response, string $idLote)
    {
        // Tentar extrair conteúdo SOAP
        if (preg_match('/<soap:Body>(.*?)<\/soap:Body>/s', $response, $matches)) {
            $bodyContent = $matches[1];
            return simplexml_load_string($bodyContent);
        }
        
        // Tentar encontrar qualquer tag XML
        if (preg_match('/<([a-z]+:)?retEnviNFe[^>]*>(.*?)<\/([a-z]+:)?retEnviNFe>/s', $response, $matches)) {
            return simplexml_load_string($matches[0]);
        }
        
        return false;
    }

    private function processarEstruturaResposta($xml, string $idLote): array
    {
        Log::info("🔍 INICIANDO processarEstruturaResposta");
        
        // Registrar namespaces
        $xml->registerXPathNamespace('soap', 'http://www.w3.org/2003/05/soap-envelope');
        $xml->registerXPathNamespace('nfe', 'http://www.portalfiscal.inf.br/nfe');
        $xml->registerXPathNamespace('', 'http://www.portalfiscal.inf.br/nfe');
        
        // Procurar diferentes estruturas de resposta
        $paths = [
            '//nfe:retEnviNFe',
            '//retEnviNFe',
            '//nfe:infProt', 
            '//infProt',
            '//nfe:protNFe',
            '//protNFe'
        ];
        
        Log::info("🔎 Procurando estruturas...");
        
        foreach ($paths as $path) {
            $result = $xml->xpath($path);
            Log::info("   🔍 Path '{$path}': " . count($result) . " resultados");
            
            if (!empty($result)) {
                Log::info("✅ Estrutura encontrada: {$path}");
                Log::info("📋 Elemento: " . $result[0]->getName());
                
                // ✅ DEBUG: Mostrar conteúdo do elemento encontrado
                $elementContent = $result[0]->asXML();
                Log::info("📄 Conteúdo do elemento (primeiros 500 chars): " . substr($elementContent, 0, 500));
                
                return $this->processarElementoEncontrado($result[0], $idLote);
            }
        }
        
        // ✅ DEBUG EXTRA: Mostrar TODOS os elementos disponíveis
        Log::info("🔍 BUSCA POR TODOS OS ELEMENTOS DISPONÍVEIS:");
        $allElements = $xml->xpath('//*');
        foreach ($allElements as $element) {
            $name = $element->getName();
            Log::info("   📍 Elemento: {$name}");
        }
        
        throw new Exception('Nenhuma estrutura conhecida encontrada na resposta');
    }

    private function processarElementoEncontrado($element, string $idLote): array
    {
        Log::info("🔧 INICIANDO processarElementoEncontrado");
        $elementName = $element->getName();
        Log::info("🎯 Processando elemento: {$elementName}");
        
        // ✅ DEBUG: Mostrar TODA a estrutura do elemento
        Log::info("📋 ESTRUTURA COMPLETA do elemento:");
        $elementXml = $element->asXML();
        Log::info("📄 XML completo:\n" . $elementXml);
        
        switch ($elementName) {
            case 'retEnviNFe':
                Log::info("🔄 Processando retEnviNFe");
                
                // ✅ VERIFICAR se tem protNFe dentro do retEnviNFe
                $protNFe = $element->protNFe;
                if ($protNFe) {
                    Log::info("✅ protNFe encontrado dentro de retEnviNFe");
                    return $this->processarElementoEncontrado($protNFe, $idLote);
                }
                
                // ✅ VERIFICAR se tem infProt dentro do retEnviNFe
                $infProt = $element->infProt;
                if ($infProt) {
                    Log::info("✅ infProt encontrado dentro de retEnviNFe");
                    return $this->processarElementoEncontrado($infProt, $idLote);
                }
                
                $infRec = $element->infRec;
                if ($infRec && (string)$infRec->cStat == '103') {
                    Log::info("📞 Lote em processamento, consultando protocolo...");
                    return $this->consultarProtocolo((string)$infRec->nRec, $idLote);
                }
                
                Log::warning("⚠️ retEnviNFe sem protNFe, infProt ou infRec conhecido");
                break;
                
            case 'protNFe':
                Log::info("🎯 Processando protNFe");
                
                // ✅ VERIFICAR se tem infProt dentro do protNFe
                $infProt = $element->infProt;
                if ($infProt) {
                    Log::info("✅ infProt encontrado dentro de protNFe");
                    return $this->processarElementoEncontrado($infProt, $idLote);
                }
                
                Log::warning("⚠️ protNFe sem infProt");
                break;
                
            case 'infProt':
                Log::info("🎯 Processando infProt");
                $cStat = (string)$element->cStat;
                $xMotivo = (string)$element->xMotivo;
                
                Log::info("📊 Status: cStat={$cStat}, xMotivo={$xMotivo}");

                // ⚙️ Detectar status de contingência (SEFAZ fora do ar)
                if (in_array($cStat, ['108', '109'])) {
                    Log::warning("⚙️ SEFAZ INDISPONÍVEL - Código {$cStat}, modo contingência ativado");
                    return [
                        'success' => false,
                        'modo_contingencia' => true,
                        'erro' => "{$cStat} - {$xMotivo}",
                        'codigo_erro' => $cStat
                    ];
                }
                
                // ✅ DEBUG: Verificar se campos existem
                $nProt = $element->nProt ? (string)$element->nProt : 'NÃO ENCONTRADO';
                $chNFe = $element->chNFe ? (string)$element->chNFe : 'NÃO ENCONTRADO';
                $digVal = $element->digVal ? (string)$element->digVal : 'NÃO ENCONTRADO';
                
                Log::info("📋 Campos extraídos: nProt={$nProt}, chNFe={$chNFe}, digVal={$digVal}");
                
                if ($cStat == '100') {
                    Log::info("🎉 NF-e AUTORIZADA - Retornando sucesso");
                    return [
                        'success' => true,
                        'numero_protocolo' => $nProt,
                        'chave_acesso' => $chNFe,
                        'digest_value' => $digVal,
                        'mensagem' => $xMotivo,
                        'data_autorizacao' => now()->format('Y-m-d H:i:s')
                    ];
                } else {
                    Log::info("❌ NF-e REJEITADA - Retornando erro");
                    return [
                        'success' => false,
                        'erro' => "{$cStat} - {$xMotivo}",
                        'codigo_erro' => $cStat
                    ];
                }
                
            default:
                Log::warning("⚠️ Elemento não tratado: {$elementName}");
                break;
        }
        
        // Se chegou aqui, não processou o elemento
        Log::error("❌ Elemento {$elementName} não foi processado corretamente");
        
        // ✅ DEBUG EXTRA: Mostrar todos os children para debug
        Log::info("🔍 CHILDRENS do elemento {$elementName}:");
        foreach ($element->children() as $child) {
            $childName = $child->getName();
            Log::info("   👶 Child: {$childName}");
        }
        
        throw new Exception("Elemento {$elementName} não suportado");
    }
    
    /**
     * Consulta protocolo de autorização
     */
    private function consultarProtocolo(string $nRec, string $idLote): array
    {
        try {
            Log::info("🔍 Consultando protocolo. nRec: {$nRec}");
            
            $response = $this->tools->sefazConsultaRecibo($nRec);
            
            $xml = simplexml_load_string($response);
            $xml->registerXPathNamespace('soap', 'http://www.w3.org/2003/05/soap-envelope');
            $xml->registerXPathNamespace('nfe', 'http://www.portalfiscal.inf.br/nfe');
            
            // Procurar protNFe
            $protNFe = $xml->xpath('//nfe:protNFe');
            if (empty($protNFe)) {
                $protNFe = $xml->xpath('//protNFe');
            }
            
            if (!empty($protNFe)) {
                $protNFe = $protNFe[0];
                $infProt = $protNFe->infProt;
                
                $cStat = (string) $infProt->cStat;
                $xMotivo = (string) $infProt->xMotivo;
                $nProt = (string) $infProt->nProt;
                $chave = (string) $infProt->chNFe;
                
                if ($cStat == '100') {
                    return [
                        'success' => true,
                        'numero_protocolo' => $nProt,
                        'chave_acesso' => $chave,
                        'mensagem' => $xMotivo,
                        'data_autorizacao' => now()->format('Y-m-d H:i:s'),
                        'xml_autorizado' => $response
                    ];
                } else {
                    return [
                        'success' => false,
                        'erro' => "{$cStat} - {$xMotivo}",
                        'codigo_erro' => $cStat
                    ];
                }
            }
            
            throw new Exception('Protocolo não encontrado na resposta');
            
        } catch (Exception $e) {
            Log::error('Erro na consulta do protocolo: ' . $e->getMessage());
            return [
                'success' => false,
                'erro' => 'Erro na consulta do protocolo: ' . $e->getMessage()
            ];
        }
    }

    private function processarRespostaConsulta(string $response): array
    {
        try {
            $xml = simplexml_load_string($response);
            $ns = $xml->getNamespaces(true);
            
            // Acessa os namespaces corretos
            $retConsSitNFe = $xml->children($ns['']);
            $infCons = $retConsSitNFe->infCons;
            
            $cStat = (string) $infCons->cStat;
            $xMotivo = (string) $infCons->xMotivo;
            
            if ($cStat == '100') { // Consulta realizada com sucesso
                $protNFe = $infCons->protNFe;
                
                return [
                    'success' => true,
                    'situacao' => (string) $protNFe->infProt->cStat,
                    'motivo' => (string) $protNFe->infProt->xMotivo,
                    'protocolo' => (string) $protNFe->infProt->nProt,
                    'chave' => (string) $protNFe->infProt->chNFe,
                    'data_autorizacao' => (string) $protNFe->infProt->dhRecbto,
                    'mensagem' => $xMotivo
                ];
            } else {
                return [
                    'success' => false,
                    'erro' => "{$cStat} - {$xMotivo}",
                    'codigo_erro' => $cStat
                ];
            }
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'erro' => 'Erro ao processar resposta da consulta: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Processa resposta do status do serviço
     */
    private function processarRespostaStatus(string $response): array
    {
        try {
            $xml = simplexml_load_string($response);
            
            // Usar XPath para encontrar o retConsStatServ independente do namespace
            $xml->registerXPathNamespace('nfe', 'http://www.portalfiscal.inf.br/nfe');
            $xml->registerXPathNamespace('soap', 'http://www.w3.org/2003/05/soap-envelope');
            
            // Procurar retConsStatServ com o namespace correto
            $retConsStatServ = $xml->xpath('//nfe:retConsStatServ');
            
            if (empty($retConsStatServ)) {
                // Tentar sem namespace
                $retConsStatServ = $xml->xpath('//retConsStatServ');
            }
            
            if (!empty($retConsStatServ)) {
                $retConsStatServ = $retConsStatServ[0];
                
                $cStat = (string) $retConsStatServ->cStat;
                $xMotivo = (string) $retConsStatServ->xMotivo;
                
                if ($cStat == '107') { // Serviço em operação
                    return [
                        'success' => true,
                        'status' => 'operacional',
                        'mensagem' => $xMotivo,
                        'ambiente' => (string) $retConsStatServ->tpAmb == '1' ? 'Produção' : 'Homologação',
                        'versao' => (string) $retConsStatServ->verAplic,
                        'data_consulta' => (string) $retConsStatServ->dhRecbto,
                        'tempo_medio' => (string) ($retConsStatServ->tMed ?? '0')
                    ];
                } else {
                    return [
                        'success' => false,
                        'status' => 'indisponivel',
                        'erro' => "{$cStat} - {$xMotivo}",
                        'codigo_erro' => $cStat
                    ];
                }
            }
            
            return [
                'success' => false,
                'erro' => 'Estrutura da resposta não reconhecida'
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'erro' => 'Erro ao processar resposta: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Extrai código de erro da mensagem
     */
    private function extrairCodigoErro(string $mensagem): ?string
    {
        // Tenta extrair código numérico do erro
        preg_match('/\[(\d+)\]/', $mensagem, $matches);
        return $matches[1] ?? null;
    }

    /**
     * Configurações da SEFA
     */
    private function getConfig(): array
    {
        return [
            "atualizacao" => date('Y-m-d H:i:s'),
            "tpAmb" => (int) config('nfe.ambiente', 1), // 1-Produção, 2-Homologação
            "razaosocial" => config('nfe.razao_social'),
            "cnpj" => config('nfe.cnpj'),
            "siglaUF" => config('nfe.uf'),
            "schemes" => "PL_009_V4",
            "versao" => "4.00",
            "tokenIBPT" => "",
            "CSC" => config('nfe.csc', ''),
            "CSCid" => config('nfe.csc_id', ''),
            "proxyConf" => [
                "proxyIp" => "",
                "proxyPort" => "",
                "proxyUser" => "",
                "proxyPass" => ""
            ]
        ];
    }

    /**
     * Carrega certificado digital
     */
    private function getCertificate(): Certificate
    {
        $certificatePath = storage_path('app/' . config('nfe.certificado_path'));
        $certificatePassword = config('nfe.certificado_senha');
        
        Log::info("🔐 Procurando certificado em: {$certificatePath}");
        
        if (!file_exists($certificatePath)) {
            throw new Exception("Certificado não encontrado: {$certificatePath}");
        }
        
        if (is_dir($certificatePath)) {
            throw new Exception("O caminho aponta para uma PASTA, não para um arquivo: {$certificatePath}");
        }
        
        if (!is_readable($certificatePath)) {
            throw new Exception("Sem permissão para ler o certificado: {$certificatePath}");
        }
        
        $conteudo = file_get_contents($certificatePath);
        if (empty($conteudo)) {
            throw new Exception("Certificado vazio ou corrompido: {$certificatePath}");
        }
        
        return Certificate::readPfx($conteudo, $certificatePassword);
    }

    public function cancelarNFe(string $chaveAcesso, string $numeroProtocolo, string $justificativa, ?string $dataAutorizacao = null): array
    {
        try {
            Log::info("🚫 Iniciando cancelamento da NF-e: {$chaveAcesso}");

            // 1️⃣ Validação do tempo (até 30 minutos)
            if ($dataAutorizacao) {
                $autorizacao = \Carbon\Carbon::parse($dataAutorizacao);
                $agora = now();

                $diffMin = $agora->diffInMinutes($autorizacao);

                if ($diffMin > 30) {
                    Log::warning("⏱️ Tentativa de cancelamento após {$diffMin} minutos (limite: 30)");
                    return [
                        'success' => false,
                        'erro' => 'Cancelamento não permitido: mais de 30 minutos após a autorização.',
                        'codigo_erro' => 'LIMITE_TEMPO'
                    ];
                }
            }

            // 2️⃣ Envio do evento de cancelamento
            Log::info("📤 Enviando evento de cancelamento para SEFAZ...");
            $response = $this->tools->sefazCancela($chaveAcesso, $justificativa, $numeroProtocolo);

            // 3️⃣ Salvar resposta bruta
            file_put_contents(storage_path("logs/resposta_cancelamento_{$chaveAcesso}.xml"), $response);
            Log::info("💾 Resposta de cancelamento salva em storage/logs/resposta_cancelamento_{$chaveAcesso}.xml");

            // 4️⃣ Processar resposta
            $xml = simplexml_load_string($response);
            if ($xml === false) {
                throw new Exception("Resposta inválida da SEFAZ no cancelamento.");
            }

            $xml->registerXPathNamespace('nfe', 'http://www.portalfiscal.inf.br/nfe');

            $infEvento = $xml->xpath('//nfe:infEvento');
            if (empty($infEvento)) {
                $infEvento = $xml->xpath('//infEvento');
            }

            if (empty($infEvento)) {
                throw new Exception("Estrutura de evento não encontrada na resposta da SEFAZ.");
            }

            $infEvento = $infEvento[0];
            $cStat = (string) ($infEvento->cStat ?? '');
            $xMotivo = (string) ($infEvento->xMotivo ?? '');
            $nProt = (string) ($infEvento->nProt ?? '');

            Log::info("📄 Cancelamento: cStat={$cStat}, xMotivo={$xMotivo}, nProt={$nProt}");

            if ($cStat == '135' || $cStat == '136') {
                Log::info("✅ NF-e cancelada com sucesso!");
                return [
                    'success' => true,
                    'chave_acesso' => $chaveAcesso,
                    'numero_protocolo_cancelamento' => $nProt,
                    'mensagem' => $xMotivo,
                    'xml_retorno' => $response,
                    'data_cancelamento' => now()->format('Y-m-d H:i:s')
                ];
            }

            return [
                'success' => false,
                'erro' => "{$cStat} - {$xMotivo}",
                'codigo_erro' => $cStat
            ];

        } catch (Exception $e) {
            Log::error("❌ Erro ao cancelar NF-e: " . $e->getMessage());
            return [
                'success' => false,
                'erro' => 'Erro no cancelamento: ' . $e->getMessage(),
                'codigo_erro' => 'EXCEPTION'
            ];
        }
    }
}