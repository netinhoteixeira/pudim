<?php

namespace Pudim\jqGrid;

/**
 * Classe jqGrid.
 */
class jqGridRegistro
{

    private $_id;
    private $_celulas;

    /**
     * Cria um registro para o jqGrid.
     * 
     * @param String $id Identificação do Registro
     */
    function __construct($id)
    {
        $this->_id = $id;
        $this->_celulas = array();
    }

    /**
     * Adicionar uma célula com o valor fornecido ou o vetor com sua chave.
     * 
     * @throws IndexNotFoundException
     */
    function adicionarCelula() //$resultado, $indice, $indice1, ...
    {
        if (func_num_args() === 0) {
            throw new ArgumentsMissingException('Precisa ser fornecido o resultado ou o vetor e chave.');
        } elseif (func_num_args() === 1) {
            array_push($this->_celulas, func_get_arg(0));
        } else {
            $resultado = func_get_arg(0);
            if (is_array($resultado)) {
                $total = func_num_args();
                for ($i = 1; $i <= $total - 1; $i++) {
                    if (isset($resultado[func_get_arg($i)])) {
                        $resultado = $resultado[func_get_arg($i)];
                    } else {
                        throw new IndexNotFoundException('$resultado[\'' . func_get_arg($i) . '\'] é um índice inválido');
                    }
                }

                array_push($this->_celulas, $resultado);
            } else {
                throw new IndexNotFoundException('O primeiro parâmetro precisa ser o resultado em si ou um vetor.');
            }
        }
    }

    function obter()
    {
        return array(
            'id' => $this->_id,
            'cell' => $this->_celulas
        );
    }

}
