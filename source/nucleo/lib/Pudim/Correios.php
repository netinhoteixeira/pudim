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
use Pudim\Texto;
use Pudim\Respostas\RespostaCorreiosEncomenda;
use Pudim\Respostas\RespostaCorreiosEncomendaItem;

class Correios
{

    private static function requisitar($url, $post, $regex)
    {
        require_once(implode(DIRECTORY_SEPARATOR, [__DIR__, '..', '..', '..', '..', 'library', 'curl_exec_utf8.func.php']));
        
        // efetua a consulta ao CEP
        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $post,
            CURLOPT_RETURNTRANSFER => true
        ]);

        $response = curl_exec_utf8($ch);
        curl_close($ch);

        // processa o retorno
        $match = null;
        preg_match_all($regex, $response, $match);

        return $match;
    }

    public static function consultarCep($cep)
    {
        $resposta = new RespostaCorreiosCep();

        // remove tudo que não for número
        $cepSanitizado = preg_replace('/[^0-9]/', '', $cep);

        $consulta = self::requisitar('http://www.buscacep.correios.com.br/servicos/dnec/consultaEnderecoAction.do', 'relaxation=' . $cepSanitizado . '&TipoCep=ALL&semelhante=N&Metodo=listaLogradouro&TipoConsulta=relaxation&StartRow=1&EndRow=10&cfm=1', '/>(.*?)<\/td>/');

        $resultado = $consulta[1];

        if (!empty($resultado[RESPOSTA_CORREIOS_CEP_UF])) {
            $resposta->setEncontrou(true);
            $resposta->setLogradouro($resultado[RESPOSTA_CORREIOS_CEP_LOGRADOURO]);
            $resposta->setBairro($resultado[RESPOSTA_CORREIOS_CEP_BAIRRO]);
            $resposta->setCidade($resultado[RESPOSTA_CORREIOS_CEP_CIDADE]);
            $resposta->setUf($resultado[RESPOSTA_CORREIOS_CEP_UF]);
            $resposta->setCep($cepSanitizado);
        }

        return $resposta;
    }

    public static function consultarEncomenda($codigo)
    {
        $resposta = new RespostaCorreiosEncomenda($codigo);

        $consulta = self::requisitar('http://websro.correios.com.br/sro_bin/txect01$.QueryList', 'P_LINGUA=001&P_TIPO=001&P_COD_UNI=' . $codigo, '/(rowspan=([0-9])>(.*?)<\/td><td>(.*?)<.*>(.*?)<\/font)|(colspan=([0-9])>(.*?)<)/i');

        $historicos = [];
        $x = -1;

        for ($i = 0; $i < sizeof($consulta[0]); $i++) {
            if ($consulta[4][$i]) {
                $x++;
                $historicos[$x]['data'] = $consulta[3][$i];
                $historicos[$x]['local'] = str_replace(' /', '/', Texto::normalizarEspacos($consulta[4][$i]));
                $historicos[$x]['situacao'] = $consulta[5][$i];
            } else {
                $historicos[$x]['detalhe'] = $consulta[8][$i];
            }
        }

        // gera os itens do histórico
        foreach ($historicos as $historico) {
            $item = new RespostaCorreiosEncomendaItem();

            if (isset($historico['data'])) {
                $item->setData($historico['data']);
            }

            if (isset($historico['local'])) {
                $item->setLocal($historico['local']);
            }

            if (isset($historico['situacao'])) {
                $item->setSituacao($historico['situacao']);
            }

            if (isset($historico['detalhe'])) {
                $item->setDetalhe($historico['detalhe']);
            }

            $resposta->adicionarHistorico($item);
        }

        return $resposta;
    }

}
