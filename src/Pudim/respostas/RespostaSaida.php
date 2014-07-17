<?php

namespace Pudim\Respostas;

/**
 * Classe RespostaSaida.
 */
class RespostaSaida implements \JsonSerializable
{

    private $_saiu = true;

    public function get_saiu()
    {
        return $this->_saiu;
    }

    public function set_saiu($_saiu)
    {
        $this->_saiu = $_saiu;
    }

    /**
     * Chamado quando executado o json_encode().
     * 
     * @return array
     */
    public function jsonSerialize()
    {
        return array(
            'saiu' => $this->_saiu
        );
    }

}
