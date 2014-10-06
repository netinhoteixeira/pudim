<?php

require_once __DIR__ . '/../source/Pudim/Arquivo.php';

use Pudim\Formulario;

class FormularioTest extends PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        Pudim\Arquivo::requererDiretorio(__DIR__ . '/../source/Pudim/');
        Pudim\Arquivo::requererDiretorio(__DIR__ . '/../source/Pudim/respostas/');
        Pudim\Arquivo::requererDiretorio(__DIR__ . '/../source/Pudim/excecoes/');
    }

    public function testValidarCpf()
    {
        echo '054.868.644-00: ' . (Formulario::validarCpf('054.868.644-00') ? 'Válido' : 'Inválido') . "\n";
        echo '05486864400: ' . (Formulario::validarCpf('05486864400') ? 'Válido' : 'Inválido') . "\n";
        echo '99999999999: ' . (Formulario::validarCpf('99999999999') ? 'Válido' : 'Inválido') . "\n";
    }

    public function testValidarCnpj()
    {
        //print_r(Formulario::consultarEncomenda('JG426220557BR'));Ï
    }

}
