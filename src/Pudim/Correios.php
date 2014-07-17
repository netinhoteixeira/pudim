<?php

namespace Pudim;

use Pudim\Respostas\RespostaCorreiosCep;

class Correios
{

    public static function consultarCep($cep)
    {
        $resposta = new RespostaCorreiosCep();

        // remove tudo que não for número
        $cep = preg_replace('/[^0-9]/', '', $cep);

        // efetua a consulta ao CEP
        $ch = curl_init();

        curl_setopt_array($ch, array(
            CURLOPT_URL => 'http://www.buscacep.correios.com.br/servicos/dnec/consultaEnderecoAction.do',
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => 'relaxation=' . $cep . '&TipoCep=ALL&semelhante=N&Metodo=listaLogradouro&TipoConsulta=relaxation&StartRow=1&EndRow=10&cfm=1',
            CURLOPT_RETURNTRANSFER => true
        ));

        $response = curl_exec($ch);
        curl_close($ch);

        $matches = null;
        preg_match_all('/>(.*?)<\/td>/', $response, $matches);

        $resultado = $matches[1];

        if (!empty($resultado[RESPOSTA_CORREIOS_CEP_UF])) {
            $resposta->setEncontrou(true);
            $resposta->setLogradouro($resultado[RESPOSTA_CORREIOS_CEP_LOGRADOURO]);
            $resposta->setBairro($resultado[RESPOSTA_CORREIOS_CEP_BAIRRO]);
            $resposta->setLocalidade($resultado[RESPOSTA_CORREIOS_CEP_LOCALIDADE]);
            $resposta->setUf($resultado[RESPOSTA_CORREIOS_CEP_UF]);
            $resposta->setCep($resultado[RESPOSTA_CORREIOS_CEP]);
        }

        return $resposta;
    }

    public static function consultarEncomenda($rastreamento)
    {
        // remove tudo que não for letra e número
        $rastreamento = preg_replace('^([a-Z]+[0-9]|[0-9]+[a-Z])[a-Z0-9]*$', '', $rastreamento);
    }

}
