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
 * Classe Configuracao.
 * Lê/Salva um arquivo de configuração no formato INI. Exemplo:
 * 
 * [secao]
 * propriedade = valor
 * ; esse é um comentário
 * propriedade = valor
 */
class Configuracao
{

    private $_arquivo;
    private $_propriedades = [];

    /**
     * Construtor da classe.
     * 
     * @param string $arquivo Caminho do arquivo
     */
    public function __construct($arquivo)
    {
        $this->_arquivo = $arquivo;
        $this->abrirArquivo($this->_arquivo);
    }

    /**
     * Retorna o caminho do arquivo.
     * 
     * @return string
     */
    public function getArquivo()
    {
        return $this->_arquivo;
    }

    /**
     * Retorna as propriedades e valores para o arquivo.
     * 
     * @return array
     */
    public function getPropriedades()
    {
        return $this->_propriedades;
    }

    /**
     * Obtém o valor de uma propriedade.
     * 
     * @param type $nome Nome da propriedade
     * @return null | anytype | boolean
     */
    public function get($nome)
    {
        $propriedades = null;

        if (strpos($nome, '.')) {
            $propriedade = explode('.', $nome);
            $secao = &$this->_propriedades[$propriedade[0]];
            $nome = $propriedade[1];
        } else {
            $secao = &$propriedades;
        }

        if (is_array($secao) && isset($secao[$nome])) {
            return $secao[$nome];
        }

        return false;
    }

    /**
     * Define um valor para a propriedade.
     * 
     * @param string $nome Nome
     * @param anytype $valor Valor
     */
    public function set($nome, $valor)
    {
        if (strpos($nome, '.')) {
            $comSecao = explode('.', $nome);
            $this->_propriedades[$comSecao[0]][$comSecao[1]] = $valor;
        } else {
            $this->_propriedades[$nome] = $valor;
        }
    }

    /**
     * Abre o arquivo de configuração.
     * 
     * @param string $arquivo Arquivo de Configuração
     */
    private function abrirArquivo($arquivo)
    {
        if (file_exists($arquivo)) {
            $secao = '';
            $linhas = file($arquivo);

            foreach ($linhas as $linha) {
                $linha = trim($linha);

                // caso a linha for em branco, não há seção
                if (empty($linha)) {
                    $secao = '';
                    continue;
                }

                // caso a linha for um comentário
                else if ($linha[0] === ';') {
                    continue;
                }

                // caso seja uma seção
                else if (($linha[0] === '[') && ($linha[strlen($linha) - 1] === ']')) {
                    $secao = substr($linha, 1, strlen($linha) - 2);
                }

                // do contrário é uma propriedade
                else {
                    $propriedade = new ConfiguracaoPropriedade($linha);
                    if (!empty($secao)) {
                        $this->_propriedades[$secao][$propriedade->getNome()] = $propriedade->getValor();
                    } else {
                        $this->_propriedades[$propriedade->getNome()] = $propriedade->getValor();
                    }
                }
            }
        }
    }

    public function persistir()
    {
        $linhas = [];
        foreach ($this->_propriedades as $chave => $valor) {
            if (is_array($valor)) {
                $this->persistirValorEmVetor($chave, $valor, $linhas);
            } else {
                $this->persistirValor($chave, $valor, $linhas);
            }
        }

        $this->salvarArquivo($this->_arquivo, implode("\r\n", $linhas));
    }

    private function persistirValorEmVetor($chave, $valor, &$linhas)
    {
        if (count($linhas) > 0) {
            if (!empty($linhas[count($linhas) - 1])) {
                // linha em branco
                $linhas[] = '';
            }
        }

        $linhas[] = '[' . $chave . ']';
        foreach ($valor as $schave => $svalor) {
            $propriedade = $schave . ' = ';

            if (is_numeric($svalor)) {
                $propriedade .= $svalor;
            } elseif (is_bool($svalor)) {
                $propriedade .= $svalor ? 'true' : 'false';
            } else {
                $propriedade .= $svalor;
            }

            $linhas[] = $propriedade;
        }

        // linha em branco
        $linhas[] = '';
    }

    private function persistirValor($chave, $valor, &$linhas)
    {
        $propriedade = $chave . ' = ';

        if (is_numeric($valor)) {
            $propriedade .= $valor;
        } elseif (is_bool($valor)) {
            $propriedade .= $valor ? 'true' : 'false';
        } else {
            $propriedade .= $valor;
        }

        $linhas[] = $propriedade;
    }

    /**
     * Salva o arquivo de configuração.
     * 
     * @param string $arquivo Arquivo de Configuração
     * @param string $dados Dados a serem salvos no Arquivo de Configuração
     */
    private function salvarArquivo($arquivo, $dados)
    {
        $apontamento = fopen($arquivo, 'w');
        if ($apontamento) {
            $inicio = microtime();

            do {
                $podeEscrever = flock($apontamento, LOCK_EX);

                // se o bloqueio não for obtido entre 0 a 100 milisegundos, para
                // previnir colisão e carregamento pela CPU
                if (!$podeEscrever) {
                    usleep(round(rand(0, 100) * 1000));
                }
            } while ((!$podeEscrever) && ((microtime() - $inicio) < 1000));

            // o arquivo estava bloqueado então agora podemos armazenar as
            // informações
            if ($podeEscrever) {
                fwrite($apontamento, $dados);
                flock($apontamento, LOCK_UN);
            }

            fclose($apontamento);
        }
    }

}
