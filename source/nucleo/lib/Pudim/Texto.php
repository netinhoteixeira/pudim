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

/**
 * Classe Texto.
 */
class Texto
{

    /**
     * Responsável por reduzir o nome com a quantidade de caracteres informada,
     * sem cortar pedaco do nome e simplificando os sobrenomes: Pedro P. Souza
     *
     * @author Tayron Miranda <falecom@tayronmiranda.com.br>
     * @param  string $nomeFornecido Nome a ser reduzido
     * @param  string $tamanhoMaximo Quantidade de caracteres
     * @return string limitado aos caracteres sem cortar palavras abrevia os
     * nomes do meio
     * @since  12/07/2010
     */
    public static function reduzirNome($nomeFornecido, $tamanhoMaximo)
    {
        $retorno = trim($nomeFornecido);

        // caso o nome fornecido seja maior que o permitido
        if (strlen($nomeFornecido) > ($tamanhoMaximo - 2)) {
            $nomeFornecido = trim(strip_tags($nomeFornecido));

            $nome = self::obterPrimeiroNome($nomeFornecido);
            $sobrenome = self::obterUltimoNome($nomeFornecido);

            // variável para receber os nomes do meio abreviados
            $meio = '';

            // lista-se todos os nomes do meio e abrevia-os
            for ($a = 1; $a < $sobrenome->posicao; $a++) {
                // enquanto o tamanho do nome não atingir o limite máximo,
                // completa-se com os nomes do meio abreviados
                $nomeCompleto = $nome . ' ' . $meio . ' ' . $sobrenome->nome;
                if (strlen($nomeCompleto) <= $tamanhoMaximo) {
                    $palavra = substr($sobrenome->palavras[$a], 0, 1);
                    $meio .= ' ' . strtoupper($palavra);
                }
            }

            $retorno = trim($nome . $meio . ' ' . $sobrenome->nome);
        }

        return $retorno;
    }

    /**
     * Obtém o primeiro nome.
     * 
     * @param string $nome Nome completo
     * @return string Primeiro nome
     */
    private static function obterPrimeiroNome($nome)
    {
        $palavras = explode(' ', $nome);
        return $palavras[0];
    }

    /**
     * Obtém o último nome.
     * 
     * @param string $nome Nome completo
     * @return string Último nome
     */
    private static function obterUltimoNome($nome)
    {
        $palavras = explode(' ', $nome);

        return (object) array(
                    'palavras' => $palavras,
                    'posicao' => count($palavras) - 1,
                    'nome' => trim($palavras[count($palavras) - 1])
        );
    }

    /**
     * Normaliza os espaços existentes em um texto.
     * 
     * @param string $texto Texto
     * @return string
     */
    public static function normalizarEspacos($texto)
    {
        return trim(preg_replace('/[\s\t\n\r\s]+/', ' ', $texto));
    }

}
