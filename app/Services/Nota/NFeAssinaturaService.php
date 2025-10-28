<?php
// app/Services/Nota/NFeAssinaturaService.php

namespace App\Services\Nota;

use NFePHP\NFe\Tools;
use NFePHP\Common\Certificate;
use Exception;

class NFeAssinaturaService
{
    private $tools;
    private $config;

    public function __construct()
    {
        $this->config = $this->getConfig();
        $this->tools = new Tools(json_encode($this->config), $this->getCertificate());
    }

    /**
     * Assina XML da NF-e
     */
    public function assinarXml(string $xml): string
    {
        try {
            return $this->tools->signNFe($xml);
        } catch (Exception $e) {
            throw new Exception("Erro na assinatura digital: " . $e->getMessage());
        }
    }

    /**
     * Configurações para SEFA
     */
    private function getConfig(): array
    {
        return [
            "atualizacao" => date('Y-m-d H:i:s'),
            "tpAmb" => (int) config('nfe.ambiente', 2),
            "razaosocial" => config('nfe.emitente_razao_social'),
            "cnpj" => config('nfe.emitente_cnpj'),
            "siglaUF" => config('nfe.emitente_uf', 'PA'),
            "schemes" => "PL_009_V4",
            "versao" => "4.00",
            "token" => "",
            "CSC" => "",
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
        $certificatePath = config('nfe.certificate_path');
        $certificatePassword = config('nfe.certificate_password');

        if (!file_exists($certificatePath)) {
            throw new Exception("Certificado digital não encontrado: {$certificatePath}");
        }

        return Certificate::readPfx(
            file_get_contents($certificatePath),
            $certificatePassword
        );
    }
}