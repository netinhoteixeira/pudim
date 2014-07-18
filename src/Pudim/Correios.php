<?php

/**
 * Pudim - Framework para desenvolvimento rápido em PHP.
 * Copyright (C) 2014  Francisco Ernesto Teixeira <fco.ernesto@gmail.com>
 * 
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

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
