<?php
// app/Services/Nota/SefazApiService.php

namespace App\Services\Nota;

use NFePHP\NFe\Tools;
use NFePHP\Common\Certificate;
use Exception;
use Illuminate\Support\Facades\Log;

class SefazApiService
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
            $idLote = date('YmdHis') . rand(100, 999);
            
            // Envia para SEFAZ
            $response = $this->tools->sefazEnviaLote(
                [$xmlAssinado],
                $idLote,
                1 // 1=Síncrono, 0=Assíncrono
            );

            return $this->processarRespostaAutorizacao($response, $idLote);

        } catch (Exception $e) {
            Log::error('Erro ao autorizar NF-e: ' . $e->getMessage());
            return [
                'success' => false,
                'erro' => 'SEFAZ: ' . $e->getMessage(),
                'codigo_erro' => $this->extrairCodigoErro($e->getMessage())
            ];
        }
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
                'erro' => 'Serviço SEFAZ indisponível: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Processa resposta da autorização
     */
    private function processarRespostaAutorizacao(string $response, string $idLote): array
    {
        $xml = simplexml_load_string($response);
        
        // Extrai dados do retorno
        $cStat = (string) $xml->protNFe->infProt->cStat;
        $xMotivo = (string) $xml->protNFe->infProt->xMotivo;
        $nProt = (string) $xml->protNFe->infProt->nProt;
        $chave = (string) $xml->protNFe->infProt->chNFe;

        if ($cStat == '100') { // Autorizado
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
            $ns = $xml->getNamespaces(true);
            
            $retConsStatServ = $xml->children($ns['']);
            $infCons = $retConsStatServ->infCons;
            
            $cStat = (string) $infCons->cStat;
            $xMotivo = (string) $infCons->xMotivo;
            
            if ($cStat == '107') { // Serviço em operação
                return [
                    'success' => true,
                    'status' => 'operacional',
                    'mensagem' => $xMotivo,
                    'ambiente' => (string) $infCons->tpAmb == '1' ? 'Produção' : 'Homologação',
                    'versao' => (string) $infCons->verAplic,
                    'data_consulta' => (string) $infCons->dhRecbto,
                    'tempo_medio' => (string) $infCons->tMed
                ];
            } else {
                return [
                    'success' => false,
                    'status' => 'indisponivel',
                    'erro' => "{$cStat} - {$xMotivo}",
                    'codigo_erro' => $cStat
                ];
            }
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'erro' => 'Erro ao processar status do serviço: ' . $e->getMessage()
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
     * Configurações da SEFAZ
     */
    private function getConfig(): array
    {
        return [
            "atualizacao" => date('Y-m-d H:i:s'),
            "tpAmb" => (int) config('nfe.ambiente', 2), // 1-Produção, 2-Homologação
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
        $certificatePath = storage_path(config('nfe.certificado_path'));
        $certificatePassword = config('nfe.certificado_senha');
        
        if (!file_exists($certificatePath)) {
            throw new Exception("Certificado digital não encontrado em: {$certificatePath}");
        }
        
        return Certificate::readPfx(file_get_contents($certificatePath), $certificatePassword);
    }
}