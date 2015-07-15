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

use Pudim\Service\ConfiguracaoService;
use Pudim\Model\DataTransferObject\ConfiguracaoPropriedade;
use Pudim\Model\DataTransferObject\ConfiguracaoComentario;

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
     * @param string $nome
     * @return boolean | object
     * @throws Exception
     */
    public function get($nome)
    {
        if (is_null($nome)) {
            throw new Exception('O nome da propriedade precisa ser fornecido.');
        }

        // Caso haja ponto de seção. Ex.: secao.propriedade
        $secao = null;
        if (strpos($nome, '.')) {
            $tmp = explode('.', $nome);
            $secao = $tmp[0];
            $nome = $tmp[1];
        }

        // Varre todas as propriedades
        foreach ($this->_propriedades as $propriedade) {
            if ((get_class($propriedade) === 'Pudim\Model\DataTransferObject\ConfiguracaoPropriedade') &&
                    ($propriedade->getNome() === $nome) &&
                    ($propriedade->getSecao() === $secao)) {
                return $propriedade->getValor();
            }
        }

        return false;
    }

    /**
     * Define um valor para a propriedade.
     * 
     * @param string $nome
     * @param object $valor
     * @throws Exception
     */
    public function set($nome, $valor)
    {
        if (is_null($nome)) {
            throw new Exception('O nome da propriedade precisa ser fornecido.');
        }

        // Caso haja ponto de seção. Ex.: secao.propriedade
        $secao = null;
        if (strpos($nome, '.')) {
            $tmp = explode('.', $nome);
            $secao = $tmp[0];
            $nome = $tmp[1];
        }

        if (!$this->definirEmPropriedadeExistente($nome, $valor, $secao)) {
            // Do contrário, a propriedade não existe
            $propriedade = new ConfiguracaoPropriedade();
            $propriedade->setSecao($secao);
            $propriedade->setNome($nome);
            $propriedade->setValor($valor);

            $this->_propriedades[] = $propriedade;
        }
    }

    /**
     * 
     * @param type $nome
     * @param type $valor
     * @param type $secao
     * @return boolean
     */
    private function definirEmPropriedadeExistente($nome, $valor, $secao)
    {
        // Varre todas as propriedades
        $total = count($this->_propriedades);
        if ($total > 0) {
            for ($i = 0; $i < $total; $i++) {
                if ((get_class($this->_propriedades[$i]) === 'Pudim\Model\DataTransferObject\ConfiguracaoPropriedade') &&
                        ($this->_propriedades[$i]->getNome() === $nome) &&
                        ($this->_propriedades[$i]->getSecao() === $secao)) {
                    $this->_propriedades[$i]->setValor($valor);

                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Abre o arquivo de configuração.
     * 
     * @param string $arquivo Arquivo de Configuração
     */
    private function abrirArquivo($arquivo)
    {
        if (file_exists($arquivo)) {
            $this->processarLinhas(file($arquivo));
        }
    }

    /**
     * 
     * @param type $linhas
     */
    private function processarLinhas($linhas)
    {
        $this->_propriedades = [];

        $secao = null;
        foreach ($linhas as $linha) {
            $linha = trim($linha);

            // Caso a linha for em branco, não há seção
            if (empty($linha)) {
                $secao = null;
                continue;
            }

            // Caso a linha for um comentário
            else if ($linha[0] === ';') {
                $comentario = new ConfiguracaoComentario();
                $comentario->setSecao($secao);
                $comentario->setValor($linha);

                $this->_propriedades[] = $comentario;
                continue;
            }

            // Caso seja uma seção
            else if (($linha[0] === '[') && ($linha[strlen($linha) - 1] === ']')) {
                $secao = $this->processarSecao($linha);
            }

            // Do contrário é uma propriedade
            else {
                $this->_propriedades[] = ConfiguracaoService::obterPropriedadeDaLinha($linha, $secao);
            }
        }
    }

    /**
     * 
     * @param type $linha
     * @return type
     */
    private function processarSecao($linha)
    {
        $valor = trim(substr($linha, 1, strlen($linha) - 2));

        if (empty($valor)) {
            return null;
        } else {
            return $valor;
        }
    }

    /**
     * Persiste o arquivo de configuração.
     */
    public function persistir()
    {
        $linhas = [];

        $secao = null;
        foreach ($this->_propriedades as $propriedade) {
            $this->persistirSecao($propriedade, $secao, $linhas);

            $classe = get_class($propriedade);

            if ($classe === 'Pudim\Model\DataTransferObject\ConfiguracaoPropriedade') {
                $linhas[] = $propriedade->getNome() . ' = ' . $this->obterValorDaPropriedade($propriedade);
            } elseif ($classe === 'Pudim\Model\DataTransferObject\ConfiguracaoComentario') {
                $linhas[] = $propriedade->getValor();
            }
        }

        $this->salvarArquivo($this->_arquivo, implode("\r\n", $linhas));
    }

    private function persistirSecao($propriedade, &$secao, &$linhas)
    {
        // Caso a seção da propriedade seja diferente da seção da propriedade
        // anterior
        if (($secao !== $propriedade->getSecao()) && (count($linhas) > 0)) {
            $linhas[] = '';
        }

        // Caso a seção da propriedade seja diferente da seção da propriedade
        // anterior e não seja nula
        if ($secao !== $propriedade->getSecao()) {
            $secao = $propriedade->getSecao();
            $this->persistirSecaoNaoNula($secao, $linhas);
        }
    }

    private function persistirSecaoNaoNula($secao, &$linhas)
    {
        if (!is_null($secao)) {
            $linhas[] = '[' . $secao . ']';
        }
    }

    /**
     * 
     * @param ConfiguracaoPropriedade $propriedade
     * @return string
     */
    private function obterValorDaPropriedade($propriedade)
    {
        if (is_bool($propriedade->getValor())) {
            return $propriedade->getValor() ? 'true' : 'false';
        } else {
            return '' . $propriedade->getValor();
        }
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
