<?php

require_once __DIR__ . '/../src/Pudim/Arquivo.php';

use Pudim\Correios;

class CorreiosTest extends PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        Pudim\Arquivo::requererDiretorio(__DIR__ . '/../src/Pudim/');
        Pudim\Arquivo::requererDiretorio(__DIR__ . '/../src/Pudim/respostas/');
        Pudim\Arquivo::requererDiretorio(__DIR__ . '/../src/Pudim/excecoes/');
    }

    public function testConsultarCep()
    {
        print_r(Correios::consultarCep('70750999'));
    }

    public function testConsultarEncomenda()
    {
        //print_r(Correios::consultarEncomenda('JG426220557BR'));
        //print_r(Correios::consultarEncomenda('JG710091345BR'));
        //print_r(Correios::consultarEncomenda('JG877967260BR'));
        print_r(Correios::consultarEncomenda('JG490873977BR'));
        //print_r(Correios::consultarEncomenda('RB201375950HK'));
    }

}
