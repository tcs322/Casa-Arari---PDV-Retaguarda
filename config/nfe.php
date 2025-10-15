<?php
// config/nfe.php

return [
    'ambiente' => env('NFE_AMBIENTE', 2), // 1=Produção, 2=Homologação
    
    // Dados do emitente
    'cnpj' => env('NFE_EMITENTE_CNPJ'),
    'razao_social' => env('NFE_EMITENTE_RAZAO_SOCIAL'),
    'nome_fantasia' => env('NFE_EMITENTE_NOME_FANTASIA'),
    'ie' => env('NFE_EMITENTE_IE'),
    'crt' => env('NFE_EMITENTE_CRT', '1'), // 1=Simples Nacional, 2=Simples Nacional excesso, 3=Regime Normal
    
    // Endereço do emitente
    'logradouro' => env('NFE_EMITENTE_LOGRADOURO'),
    'numero' => env('NFE_EMITENTE_NUMERO'),
    'bairro' => env('NFE_EMITENTE_BAIRRO'),
    'codigo_municipio' => env('NFE_EMITENTE_CODIGO_MUNICIPIO'),
    'municipio' => env('NFE_EMITENTE_MUNICIPIO'),
    'uf' => env('NFE_EMITENTE_UF'),
    'cep' => env('NFE_EMITENTE_CEP'),
    'telefone' => env('NFE_EMITENTE_TELEFONE'),
    
    // Certificado digital e segurança
    'csc' => env('NFE_CSC', ''),
    'csc_id' => env('NFE_CSC_ID', ''),
    'certificado_path' => env('NFE_CERTIFICADO_PATH', ''),
    'certificado_senha' => env('NFE_CERTIFICADO_SENHA', ''),
    
    // Configurações padrão para geração de NF-e
    'serie' => env('NFE_SERIE', '1'),
    'numero_inicial' => env('NFE_NUMERO_INICIAL', '1'),
    'forma_pagamento' => env('NFE_FORMA_PAGAMENTO', '0'), // 0=Pagamento à vista
    'modelo' => env('NFE_MODELO', '55'), // 55=NF-e, 65=NFC-e
];