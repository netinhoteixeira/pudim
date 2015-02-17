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
            $resposta->setCidade($resultado[RESPOSTA_CORREIOS_CEP_CIDADE]);
            $resposta->setUf($resultado[RESPOSTA_CORREIOS_CEP_UF]);
            $resposta->setCep($cep);
        }

        return $resposta;
    }

    public static function consultarEncomenda($codigo)
    {
        $resposta = new RespostaCorreiosEncomenda($codigo);

        // efetua o rastreamento da encomenda
        $ch = curl_init();

        curl_setopt_array($ch, array(
            CURLOPT_URL => 'http://websro.correios.com.br/sro_bin/txect01$.QueryList',
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => 'P_LINGUA=001&P_TIPO=001&P_COD_UNI=' . $codigo,
            CURLOPT_RETURNTRANSFER => true
        ));

        $response = curl_exec($ch);
        curl_close($ch);

        // processa o retorno
        $regex = '/(rowspan=([0-9])>(.*?)<\/td><td>(.*?)<.*>(.*?)<\/font)|(colspan=([0-9])>(.*?)<)/i';
        $match = null;
        preg_match_all($regex, $response, $match);

        $historicos = array();
        $x = -1;

        for ($i = 0; $i < sizeof($match[0]); $i++) {
            if ($match[4][$i]) {
                $x++;
                $historicos[$x]['data'] = $match[3][$i];
                $historicos[$x]['local'] = str_replace(' /', '/', Texto::normalizarEspacos($match[4][$i]));
                $historicos[$x]['situacao'] = $match[5][$i];
            } else {
                $historicos[$x]['detalhe'] = $match[8][$i];
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

    /**
     * Lista de serviços dos Correios.
     *
     * http://www.correios.com.br/para-voce/precisa-de-ajuda/como-rastrear-um-objeto/siglas-utilizadas-no-rastreamento-de-objeto
     * Última atualização em 18/07/2014
     */
    public static $servicos = array(
        'AL' => 'AGENTES DE LEITURA',
        'AR' => 'AVISO DE RECEBIMENTO',
        'AS' => 'ENCOMENDA PAC – AÇÃO SOCIAL',
        'CA' => 'OBJETO INTERNACIONAL',
        'CB' => 'OBJETO INTERNACIONAL',
        'CC' => 'COLIS POSTAUX',
        'CD' => 'OBJETO INTERNACIONAL',
        'CE' => 'OBJETO INTERNACIONAL',
        'CF' => 'OBJETO INTERNACIONAL',
        'CG' => 'OBJETO INTERNACIONAL',
        'CH' => 'OBJETO INTERNACIONAL',
        'CI' => 'OBJETO INTERNACIONAL',
        'CJ' => 'REGISTRADO INTERNACIONAL',
        'CK' => 'OBJETO INTERNACIONAL',
        'CL' => 'OBJETO INTERNACIONAL',
        'CM' => 'OBJETO INTERNACIONAL',
        'CN' => 'OBJETO INTERNACIONAL',
        'CO' => 'OBJETO INTERNACIONAL',
        'CP' => 'COLIS POSTAUX',
        'CQ' => 'OBJETO INTERNACIONAL',
        'CR' => 'CARTA REGISTRADA SEM VALOR DECLARADO',
        'CS' => 'OBJETO INTERNACIONAL',
        'CT' => 'OBJETO INTERNACIONAL',
        'CU' => 'OBJETO INTERNACIONAL',
        'CV' => 'REGISTRADO INTERNACIONAL',
        'CW' => 'OBJETO INTERNACIONAL',
        'CX' => 'OBJETO INTERNACIONAL',
        'CY' => 'OBJETO INTERNACIONAL',
        'CZ' => 'OBJETO INTERNACIONAL',
        'DA' => 'REM EXPRES COM AR DIGITAL',
        'DB' => 'REM EXPRES COM AR DIGITAL BRADESCO',
        'DC' => 'REM EXPRESSA CRLV/CRV/CNH e NOTIFICAÇÃO',
        'DD' => 'DEVOLUÇÃO DE DOCUMENTOS',
        'DE' => 'REMESSA EXPRESSA TALÃO E CARTÃO C/ AR',
        'DF' => 'E-SEDEX (LÓGICO)',
        'DI' => 'REM EXPRES COM AR DIGITAL ITAU',
        'DL' => 'ENCOMENDA SEDEX (LÓGICO)',
        'DP' => 'REM EXPRES COM AR DIGITAL PRF',
        'DS' => 'REM EXPRES COM AR DIGITAL SANTANDER',
        'DT' => 'REMESSA ECON.SEG.TRANSITO C/AR DIGITAL',
        'DX' => 'ENCOMENDA SEDEX 10 (LÓGICO)',
        'EA' => 'OBJETO INTERNACIONAL',
        'EB' => 'OBJETO INTERNACIONAL',
        'EC' => 'ENCOMENDA PAC',
        'ED' => 'OBJETO INTERNACIONAL',
        'EE' => 'SEDEX INTERNACIONAL',
        'EF' => 'OBJETO INTERNACIONAL',
        'EG' => 'OBJETO INTERNACIONAL',
        'EH' => 'ENCOMENDA NORMAL COM AR DIGITAL',
        'EI' => 'OBJETO INTERNACIONAL',
        'EJ' => 'ENCOMENDA INTERNACIONAL',
        'EK' => 'OBJETO INTERNACIONAL',
        'EL' => 'OBJETO INTERNACIONAL',
        'EM' => 'OBJETO INTERNACIONAL',
        'EN' => 'ENCOMENDA NORMAL NACIONAL',
        'EO' => 'OBJETO INTERNACIONAL',
        'EP' => 'OBJETO INTERNACIONAL',
        'EQ' => 'ENCOMENDA SERVIÇO NÃO EXPRESSA ECT',
        'ER' => 'REGISTRADO',
        'ES' => 'e-SEDEX',
        'ET' => 'OBJETO INTERNACIONAL',
        'EU' => 'OBJETO INTERNACIONAL',
        'EV' => 'OBJETO INTERNACIONAL',
        'EW' => 'OBJETO INTERNACIONAL',
        'EX' => 'OBJETO INTERNACIONAL',
        'EY' => 'OBJETO INTERNACIONAL',
        'EZ' => 'OBJETO INTERNACIONAL',
        'FA' => 'FAC REGISTRATO (LÓGICO)',
        'FE' => 'ENCOMENDA FNDE',
        'FF' => 'REGISTRADO DETRAN',
        'FH' => 'REGISTRADO FAC COM AR DIGITAL',
        'FM' => 'REGISTRADO - FAC MONITORADO',
        'FR' => 'REGISTRADO FAC',
        'IA' => 'INTEGRADA AVULSA',
        'IC' => 'INTEGRADA A COBRAR',
        'ID' => 'INTEGRADA DEVOLUCAO DE DOCUMENTO',
        'IE' => 'INTEGRADA ESPECIAL',
        'IF' => 'CPF',
        'II' => 'INTEGRADA INTERNO',
        'IK' => 'INTEGRADA COM COLETA SIMULTANEA',
        'IM' => 'INTEGRADA MEDICAMENTOS',
        'IN' => 'OBJ DE CORRESP E EMS REC EXTERIOR',
        'IP' => 'INTEGRADA PROGRAMADA',
        'IR' => 'IMPRESSO REGISTRADO',
        'IS' => 'INTEGRADA STANDARD',
        'IT' => 'INTEGRADO TERMOLÁBIL',
        'IU' => 'INTEGRADA URGENTE',
        'JA' => 'REMESSA ECONOMICA C/AR DIGITAL',
        'JB' => 'REMESSA ECONOMICA C/AR DIGITAL',
        'JC' => 'REMESSA ECONOMICA C/AR DIGITAL',
        'JD' => 'REMESSA ECONÔMICA S/ AR DIGITAL',
        'JE' => 'REMESSA ECONÔMICA C/ AR DIGITAL',
        'JG' => 'REGISTRATO AGÊNCIA (FÍSICO)',
        'JJ' => 'REGISTRADO JUSTIÇA',
        'JL' => 'OBJETO REGISTRADO (LÓGICO)',
        'JM' => 'MALA DIRETA POSTAL ESPECIAL (LÓGICO)',
        'LA' => 'LOGÍSTICA REVERSA SIMULTÂNEA - ENCOMENDA SEDEX (AGÊNCIA)',
        'LB' => 'LOGÍSTICA REVERSA SIMULTÂNEA - ENCOMENDA e-SEDEX (AGÊNCIA)',
        'LC' => 'CARTA EXPRESSA',
        'LE' => 'LOGÍSTICA REVERSA ECONOMICA',
        'LP' => 'LOGÍSTICA REVERSA SIMULTÂNEA - ENCOMENDA PAC (AGÊNCIA)',
        'LS' => 'LOGISTICA REVERSA SEDEX',
        'LV' => 'LOGISTICA REVERSA EXPRESSA',
        'LX' => 'CARTA EXPRESSA',
        'LY' => 'CARTA EXPRESSA',
        'MA' => 'SERVIÇOS ADICIONAIS',
        'MB' => 'TELEGRAMA DE BALCÃO',
        'MC' => 'MALOTE CORPORATIVO',
        'ME' => 'TELEGRAMA',
        'MF' => 'TELEGRAMA FONADO',
        'MK' => 'TELEGRAMA CORPORATIVO',
        'MM' => 'TELEGRAMA GRANDES CLIENTES',
        'MP' => 'TELEGRAMA PRÉ-PAGO',
        'MS' => 'ENCOMENDA SAUDE',
        'MT' => 'TELEGRAMA VIA TELEMAIL',
        'MY' => 'TELEGRAMA INTERNACIONAL ENTRANTE',
        'MZ' => 'TELEGRAMA VIA CORREIOS ON LINE',
        'NE' => 'TELE SENA RESGATADA',
        'PA' => 'PASSAPORTE',
        'PB' => 'ENCOMENDA PAC - NÃO URGENTE',
        'PC' => 'ENCOMENDA PAC A COBRAR',
        'PD' => 'ENCOMENDA PAC - NÃO URGENTE',
        'PF' => 'PASSAPORTE',
        'PG' => 'ENCOMENDA PAC (ETIQUETA FÍSICA)',
        'PH' => 'ENCOMENDA PAC (ETIQUETA LÓGICA)',
        'PR' => 'REEMBOLSO POSTAL - CLIENTE AVULSO',
        'RA' => 'REGISTRADO PRIORITÁRIO',
        'RB' => 'CARTA REGISTRADA',
        'RC' => 'CARTA REGISTRADA COM VALOR DECLARADO',
        'RD' => 'REMESSA ECONOMICA DETRAN',
        'RE' => 'REGISTRADO ECONÔMICO',
        'RF' => 'OBJETO DA RECEITA FEDERAL',
        'RG' => 'REGISTRADO DO SISTEMA SARA',
        'RH' => 'REGISTRADO COM AR DIGITAL',
        'RI' => 'REGISTRADO',
        'RJ' => 'REGISTRADO AGÊNCIA',
        'RK' => 'REGISTRADO AGÊNCIA',
        'RL' => 'REGISTRADO LÓGICO',
        'RM' => 'REGISTRADO AGÊNCIA',
        'RN' => 'REGISTRADO AGÊNCIA',
        'RO' => 'REGISTRADO AGÊNCIA',
        'RP' => 'REEMBOLSO POSTAL - CLIENTE INSCRITO',
        'RQ' => 'REGISTRADO AGÊNCIA',
        'RR' => 'CARTA REGISTRADA SEM VALOR DECLARADO',
        'RS' => 'REGISTRADO LÓGICO',
        'RT' => 'REM ECON TALAO/CARTAO SEM AR DIGITAL',
        'RU' => 'REGISTRADO SERVIÇO ECT',
        'RV' => 'REM ECON CRLV/CRV/CNH COM AR DIGITAL',
        'RY' => 'REM ECON TALAO/CARTAO COM AR DIGITAL',
        'RZ' => 'REGISTRADO',
        'SA' => 'SEDEX ANOREG',
        'SB' => 'SEDEX 10 AGÊNCIA (FÍSICO)',
        'SC' => 'SEDEX A COBRAR',
        'SD' => 'REMESSA EXPRESSA DETRAN',
        'SE' => 'ENCOMENDA SEDEX',
        'SF' => 'SEDEX AGÊNCIA',
        'SG' => 'SEDEX DO SISTEMA SARA',
        'RH' => 'REGISTRADO COM AR DIGITAL',
        'SI' => 'SEDEX AGÊNCIA',
        'SJ' => 'SEDEX HOJE',
        'SK' => 'SEDEX AGÊNCIA',
        'SL' => 'SEDEX LÓGICO',
        'SM' => 'SEDEX MESMO DIA',
        'SN' => 'SEDEX COM VALOR DECLARADO',
        'SO' => 'SEDEX AGÊNCIA',
        'SP' => 'SEDEX PRÉ-FRANQUEADO',
        'SQ' => 'SEDEX',
        'SR' => 'SEDEX',
        'SS' => 'SEDEX FÍSICO',
        'ST' => 'REM EXPRES TALAO/CARTAO SEM AR DIGITAL',
        'SU' => 'ENCOMENDA SERVIÇO EXPRESSA ECT',
        'SV' => 'REM EXPRES CRLV/CRV/CNH COM AR DIGITAL',
        'SW' => 'e-SEDEX',
        'SX' => 'SEDEX 10',
        'SY' => 'REM EXPRES TALAO/CARTAO COM AR DIGITAL',
        'SZ' => 'SEDEX AGÊNCIA',
        'TE' => 'TESTE (OBJETO PARA TREINAMENTO)',
        'TS' => 'TESTE (OBJETO PARA TREINAMENTO)',
        'VA' => 'ENCOMENDAS COM VALOR DECLARADO',
        'VC' => 'ENCOMENDAS',
        'VD' => 'ENCOMENDAS COM VALOR DECLARADO',
        'VE' => 'ENCOMENDAS',
        'VF' => 'ENCOMENDAS COM VALOR DECLARADO',
        'XM' => 'SEDEX MUNDI',
        'XR' => 'ENCOMENDA SUR POSTAL EXPRESSO',
        'XX' => 'ENCOMENDA SUR POSTAL 24 HORAS'
    );

    /**
     * Lista de países e respectivas siglas CFE ISO_3166-1
     */
    public static $paises = array(
        'AF' => 'Afeganistão',
        'ZA' => 'África do Sul',
        'AX' => 'Åland, Ilhas',
        'AL' => 'Albânia',
        'DE' => 'Alemanha',
        'AD' => 'Andorra',
        'AO' => 'Angola',
        'AI' => 'Anguilla',
        'AQ' => 'Antárctida',
        'AG' => 'Antígua e Barbuda',
        'AN' => 'Antilhas Holandesas',
        'SA' => 'Arábia Saudita',
        'DZ' => 'Argélia',
        'AR' => 'Argentina',
        'AM' => 'Armênia',
        'AW' => 'Aruba',
        'AU' => 'Austrália',
        'AT' => 'Áustria',
        'AZ' => 'Azerbaijão',
        'BS' => 'Bahamas',
        'BH' => 'Bahrain',
        'BD' => 'Bangladesh',
        'BB' => 'Barbados',
        'BE' => 'Bélgica',
        'BZ' => 'Belize',
        'BJ' => 'Benim',
        'BM' => 'Bermudas',
        'BY' => 'Bielorrússia',
        'BO' => 'Bolívia',
        'BA' => 'Bósnia e Herzegovina',
        'BW' => 'Botswana',
        'BV' => 'Bouvet, Ilha',
        'BR' => 'Brasil',
        'BN' => 'Brunei',
        'BG' => 'Bulgária',
        'BF' => 'Burkina Faso',
        'BI' => 'Burundi',
        'BT' => 'Butão',
        'CV' => 'Cabo Verde',
        'KH' => 'Cambodja',
        'CM' => 'Camarões',
        'CA' => 'Canadá',
        'KY' => 'Cayman, Ilhas',
        'KZ' => 'Cazaquistão',
        'CF' => 'Centro-Africana, República',
        'TD' => 'Chade',
        'CZ' => 'Checa, República',
        'CL' => 'Chile',
        'CN' => 'China',
        'CY' => 'Chipre',
        'CX' => 'Christmas, Ilha',
        'CC' => 'Cocos, Ilhas',
        'CO' => 'Colômbia',
        'KM' => 'Comores',
        'CG' => 'Congo, República do',
        'CD' => 'Congo, República Democrática do (antigo Zaire)',
        'CK' => 'Cook, Ilhas',
        'KR' => 'Coréia do Sul',
        'KP' => 'Coreia, República Democrática da (Coreia do Norte)',
        'CI' => 'Costa do Marfim',
        'CR' => 'Costa Rica',
        'HR' => 'Croácia',
        'CU' => 'Cuba',
        'DK' => 'Dinamarca',
        'DJ' => 'Djibouti',
        'DM' => 'Dominica',
        'DO' => 'Dominicana, República',
        'EG' => 'Egito',
        'SV' => 'El Salvador',
        'AE' => 'Emirados Árabes Unidos',
        'EC' => 'Equador',
        'ER' => 'Eritreia',
        'SK' => 'Eslováquia',
        'SI' => 'Eslovênia',
        'ES' => 'Espanha',
        'US' => 'Estados Unidos da América',
        'EE' => 'Estónia',
        'ET' => 'Etiópia',
        'FO' => 'Feroé, Ilhas',
        'FJ' => 'Fiji',
        'PH' => 'Filipinas',
        'FI' => 'Finlândia',
        'FR' => 'França',
        'GA' => 'Gabão',
        'GM' => 'Gâmbia',
        'GH' => 'Gana',
        'GE' => 'Geórgia',
        'GS' => 'Geórgia do Sul e Sandwich do Sul, Ilhas',
        'GI' => 'Gibraltar',
        'GR' => 'Grécia',
        'GD' => 'Grenada',
        'GL' => 'Groenlândia',
        'GP' => 'Guadalupe',
        'GU' => 'Guam',
        'GT' => 'Guatemala',
        'GG' => 'Guernsey',
        'GY' => 'Guiana',
        'GF' => 'Guiana Francesa',
        'GW' => 'Guiné-Bissau',
        'GN' => 'Guiné-Conacri',
        'GQ' => 'Guiné Equatorial',
        'HT' => 'Haiti',
        'HM' => 'Heard e Ilhas McDonald, Ilha',
        'HN' => 'Honduras',
        'HK' => 'Hong Kong',
        'HU' => 'Hungria',
        'YE' => 'Iémen',
        'IN' => 'Índia',
        'ID' => 'Indonésia',
        'IQ' => 'Iraque',
        'IR' => 'Irã',
        'IE' => 'Irlanda',
        'IS' => 'Islândia',
        'IL' => 'Israel',
        'IT' => 'Itália',
        'JM' => 'Jamaica',
        'JP' => 'Japão',
        'JE' => 'Jersey',
        'JO' => 'Jordânia',
        'KI' => 'Kiribati',
        'KW' => 'Kuwait',
        'LA' => 'Laos',
        'LS' => 'Lesoto',
        'LV' => 'Letônia',
        'LB' => 'Líbano',
        'LR' => 'Libéria',
        'LY' => 'Líbia',
        'LI' => 'Liechtenstein',
        'LT' => 'Lituânia',
        'LU' => 'Luxemburgo',
        'MO' => 'Macau',
        'MK' => 'Macedônia, República da',
        'MG' => 'Madagáscar',
        'MY' => 'Malásia',
        'MW' => 'Malawi',
        'MV' => 'Maldivas',
        'ML' => 'Mali',
        'MT' => 'Malta',
        'FK' => 'Malvinas, Ilhas (Falkland)',
        'IM' => 'Man, Ilha de',
        'MP' => 'Marianas Setentrionais',
        'MA' => 'Marrocos',
        'MH' => 'Marshall, Ilhas',
        'MQ' => 'Martinica',
        'MU' => 'Maurícia',
        'MR' => 'Mauritânia',
        'YT' => 'Mayotte',
        'UM' => 'Menores Distantes dos Estados Unidos, Ilhas',
        'MX' => 'México',
        'MM' => 'Myanmar (antiga Birmânia)',
        'FM' => 'Micronésia, Estados Federados da',
        'MZ' => 'Moçambique',
        'MD' => 'Moldávia',
        'MC' => 'Mônaco',
        'MN' => 'Mongólia',
        'ME' => 'Montenegro',
        'MS' => 'Montserrat',
        'NA' => 'Namíbia',
        'NR' => 'Nauru',
        'NP' => 'Nepal',
        'NI' => 'Nicarágua',
        'NE' => 'Níger',
        'NG' => 'Nigéria',
        'NU' => 'Niue',
        'NF' => 'Norfolk, Ilha',
        'NO' => 'Noruega',
        'NC' => 'Nova Caledônia',
        'NZ' => 'Nova Zelândia (Aotearoa)',
        'OM' => 'Oman',
        'NL' => 'Países Baixos (Holanda)',
        'PW' => 'Palau',
        'PS' => 'Palestina',
        'PA' => 'Panamá',
        'PG' => 'Papua-Nova Guiné',
        'PK' => 'Paquistão',
        'PY' => 'Paraguai',
        'PE' => 'Peru',
        'PN' => 'Pitcairn',
        'PF' => 'Polinésia Francesa',
        'PL' => 'Polônia',
        'PR' => 'Porto Rico',
        'PT' => 'Portugal',
        'QA' => 'Qatar',
        'KE' => 'Quênia',
        'KG' => 'Quirguistão',
        'GB' => 'Reino Unido da Grã-Bretanha e Irlanda do Norte',
        'RE' => 'Reunião',
        'RO' => 'Romênia',
        'RW' => 'Ruanda',
        'RU' => 'Rússia',
        'EH' => 'Saara Ocidental',
        'AS' => 'Samoa Americana',
        'WS' => 'Samoa (Samoa Ocidental)',
        'PM' => 'Saint Pierre et Miquelon',
        'SB' => 'Salomão, Ilhas',
        'KN' => 'São Cristóvão e Névis (Saint Kitts e Nevis)',
        'SM' => 'San Marino',
        'ST' => 'São Tomé e Príncipe',
        'VC' => 'São Vicente e Granadinas',
        'SH' => 'Santa Helena',
        'LC' => 'Santa Lúcia',
        'SN' => 'Senegal',
        'SL' => 'Serra Leoa',
        'RS' => 'Sérvia',
        'SC' => 'Seychelles',
        'SG' => 'Singapura',
        'SY' => 'Síria',
        'SO' => 'Somália',
        'LK' => 'Sri Lanka',
        'SZ' => 'Suazilândia',
        'SD' => 'Sudão',
        'SE' => 'Suécia',
        'CH' => 'Suíça',
        'SR' => 'Suriname',
        'SJ' => 'Svalbard e Jan Mayen',
        'TH' => 'Tailândia',
        'TW' => 'Taiwan',
        'TJ' => 'Tajiquistão',
        'TZ' => 'Tanzânia',
        'TF' => 'Terras Austrais e Antárticas Francesas (TAAF)',
        'IO' => 'Território Britânico do Oceano Índico',
        'TL' => 'Timor-Leste',
        'TG' => 'Togo',
        'TK' => 'Toquelau',
        'TO' => 'Tonga',
        'TT' => 'Trindade e Tobago',
        'TN' => 'Tunísia',
        'TC' => 'Turks e Caicos',
        'TM' => 'Turquemenistão',
        'TR' => 'Turquia',
        'TV' => 'Tuvalu',
        'UA' => 'Ucrânia',
        'UG' => 'Uganda',
        'UY' => 'Uruguai',
        'UZ' => 'Usbequistão',
        'VU' => 'Vanuatu',
        'VA' => 'Vaticano',
        'VE' => 'Venezuela',
        'VN' => 'Vietnam',
        'VI' => 'Virgens Americanas, Ilhas',
        'VG' => 'Virgens Britânicas, Ilhas',
        'WF' => 'Wallis e Futuna',
        'ZM' => 'Zâmbia',
        'ZW' => 'Zimbabwe'
    );

    /**
     * Situações da Encomenda.
     *
     * @var array
     */
    public static $situacoes = array(
        'POSTADO DEPOIS' => '#e4e490',
        'POSTADO' => '#ffff9f', // acceptance
        'SAIU PARA ENTREGA' => '#ddeffc', // delivering
        'ENTREGA EFETUADA' => '#e8f5b6', // delivered
        'ENCAMINHADO' => '#fff8bb', // enroute
        'DESCONHECIDO' => '#f4e5ff' // unknown
    );

    /*
      } else if (data[i].status === 'checked') {
      div.css('background-color', '#fffcd1');
      } else if (data[i].status === 'awaiting') {
      div.css('background-color', '#fff0e0');
     */
}
