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

namespace Pudim\jqGrid;

/**
 * Classe jqGrid.
 */
class jqGrid
{

    private $_paginaAtual;
    private $_totalPaginas;
    private $_totalRegistros;
    private $_registros;

    /**
     * Processa as informações recebidas do jqGrid.
     * 
     * @global DocumentManager $documentos Gerenciador de Conexões
     * @param Function $obterTotalRegistros
     * @param Function $consulta
     * @param Function $processarResultado
     * @param Object $usuarioSessao
     */
    function __construct($obterTotalRegistros, $consulta, $processarResultado, $usuarioSessao = null)
    {
        global $documentos;

        $this->obterTotalRegistros = $obterTotalRegistros;
        $this->usuarioSessao = $usuarioSessao;

        // obtém os parâmetros do jqGrid
        $paginaAtualInicial = obterVariavelGet('page');
        $limiteInicial = obterVariavelGet('rows');
        $ordenacaoInicial = obterVariavelGet('sidx');
        $direcaoInicial = obterVariavelGet('sord');

        // prepara os parâmetros obtidos
        $this->_paginaAtual = (!$paginaAtualInicial) ? 1 : $paginaAtualInicial;
        $limiteAtual = (!$limiteInicial) ? 10 : $limiteInicial;
        $ordenacao = (!$ordenacaoInicial) ? 'cadastro' : $ordenacaoInicial;
        $direcao = (!$direcaoInicial) ? 'asc' /* desc */ :
                $direcaoInicial;

        // obtém a quantidade de registros total
        if (!is_null($usuarioSessao)) {
            $this->_totalRegistros = $obterTotalRegistros($documentos, $usuarioSessao);
        } else {
            $this->_totalRegistros = $obterTotalRegistros($documentos);
        }
        $this->_totalRegistros = $this->_totalRegistros
                // contagem dos registros
                ->count()
                ->getQuery()
                ->execute();

        // com a quantidade de registros total, calcula a quantidade de páginas
        if (($this->_totalRegistros > 0) && ($limiteAtual > 0)) {
            $this->_totalPaginas = ceil($this->_totalRegistros / $limiteAtual);
        } else {
            $this->_totalPaginas = 0;
        }

        // valida a página atual com a quantidade de páginas
        if ($this->_paginaAtual > $this->_totalPaginas) {
            $this->_paginaAtual = $this->_totalPaginas;
        }

        // detecta o início dos registros
        $inicio = ($limiteAtual * $this->_paginaAtual) - $limiteAtual;
        if ($inicio < 0) {
            $inicio = 0;
        }

        // obtém os resultados paginados
        if (!is_null($usuarioSessao)) {
            $resultados = $consulta($documentos, $usuarioSessao);
        } else {
            $resultados = $consulta($documentos);
        }
        $resultados = $resultados
                ->sort($ordenacao, $direcao)
                ->skip($inicio)
                ->limit($limiteAtual)
                ->hydrate(false)
                ->getQuery()
                ->execute();

        // processa os resultados
        $this->_registros = [];
        foreach ($resultados as $resultado) {
            array_push($this->_registros, $processarResultado($resultado, $documentos));
        }
    }

    /**
     * Obtém todos os registros de acordo com os parâmetros do jqGrid.
     * 
     * @return object
     */
    function obterRegistros()
    {
        $rows = [];
        foreach ($this->_registros as $registro) {
            array_push($rows, $registro->obter());
        }

        return (object) [
                    'page' => $this->_paginaAtual,
                    'total' => $this->_totalPaginas,
                    'records' => $this->_totalRegistros,
                    'rows' => $rows
        ];
    }

}
