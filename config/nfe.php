<?php
// config/nfe.php

return [
    /*
    |--------------------------------------------------------------------------
    | Ambiente SEFA
    |--------------------------------------------------------------------------
    | 1 = Produção, 2 = Homologação
    */
    'ambiente' => env('NFE_AMBIENTE', 1),
    
    /*
    |--------------------------------------------------------------------------
    | Dados do Emitente
    |--------------------------------------------------------------------------
    */
    'cnpj' => env('NFE_EMITENTE_CNPJ'),
    'razao_social' => env('NFE_EMITENTE_RAZAO_SOCIAL'),
    'nome_fantasia' => env('NFE_EMITENTE_NOME_FANTASIA'),
    'ie' => env('NFE_EMITENTE_IE'),
    'crt' => env('NFE_EMITENTE_CRT', '1'),
    
    /*
    |--------------------------------------------------------------------------
    | Endereço do Emitente
    |--------------------------------------------------------------------------
    */
    'logradouro' => env('NFE_EMITENTE_LOGRADOURO'),
    'numero' => env('NFE_EMITENTE_NUMERO'),
    'bairro' => env('NFE_EMITENTE_BAIRRO'),
    'codigo_municipio' => env('NFE_EMITENTE_CODIGO_MUNICIPIO'),
    'municipio' => env('NFE_EMITENTE_MUNICIPIO'),
    'uf' => env('NFE_EMITENTE_UF'),
    'cep' => env('NFE_EMITENTE_CEP'),
    'telefone' => env('NFE_EMITENTE_TELEFONE'),
    
    /*
    |--------------------------------------------------------------------------
    | Certificado Digital e Segurança
    |--------------------------------------------------------------------------
    */
    'csc' => env('NFE_CSC', ''),
    'csc_id' => env('NFE_CSC_ID', ''),
    'certificado_path' => env('NFE_CERTIFICADO_PATH', ''),
    'certificado_senha' => env('NFE_CERTIFICADO_SENHA', ''),
    
    /*
    |--------------------------------------------------------------------------
    | Configurações SEFA - WebService
    |--------------------------------------------------------------------------
    */
    'webservice' => [
        'homologacao' => env('NFE_WS_HOMOLOGACAO', 'https://hom.sefa.br/ws/nfe'),
        'producao' => env('NFE_WS_PRODUCAO', 'https://www.sefa.br/ws/nfe'),
        'timeout' => env('NFE_WS_TIMEOUT', 30),
        'versao' => '4.00',
    ],
    
    /*
    |--------------------------------------------------------------------------
    | Configurações Padrão NF-e
    |--------------------------------------------------------------------------
    */
    'serie' => env('NFE_SERIE', '1'),
    'numero_inicial' => env('NFE_NUMERO_INICIAL', '1'),
    'forma_pagamento' => env('NFE_FORMA_PAGAMENTO', '0'),
    'modelo' => env('NFE_MODELO', '55'),
    
    /*
    |--------------------------------------------------------------------------
    | Configurações de Log
    |--------------------------------------------------------------------------
    */
    'log' => [
        'habilitado' => env('NFE_LOG_HABILITADO', true),
        'nivel' => env('NFE_LOG_NIVEL', 'debug'),
        'pasta' => env('NFE_LOG_PASTA', storage_path('logs/nfe')),
    ],
];