<?php

require_once implode(DIRECTORY_SEPARATOR, [__DIR__, '..', 'source', 'nucleo', 'lib', 'Pudim', 'Arquivo.php']);

use Pudim\Texto;

class TextoTest extends PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        Pudim\Arquivo::requererDiretorio(implode(DIRECTORY_SEPARATOR, [__DIR__, '..', 'source']));
    }

    public function testReduzirNome()
    {
        echo Texto::reduzirNome("Francisco Ernesto Díaz Teixeira Neto", 30). "\n";
        echo Texto::reduzirNome("Karlla Smaile Alves de Lira", 20). "\n";
    }
    
    public function testNormalizarEspacos()
    {
        echo Texto::normalizarEspacos("Este   texto    tem       muito  espaços") . "\n";
    }

}
