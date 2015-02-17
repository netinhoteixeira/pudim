<?php

require_once implode(DIRECTORY_SEPARATOR, [__DIR__, '..', 'source', 'nucleo', 'lib', 'Pudim', 'Arquivo.php']);

use Pudim\Formulario;

class FormularioTest extends PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        Pudim\Arquivo::requererDiretorio(implode(DIRECTORY_SEPARATOR, [__DIR__, '..', 'source']));
        Pudim\Arquivo::requererDiretorio(implode(DIRECTORY_SEPARATOR, [__DIR__, '..', 'example', 'Domain', 'Entity']));

        if (!defined('TMPDIR')) {
            define('TMPDIR', __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'tmp');
            if (!file_exists(TMPDIR)) {
                mkdir(TMPDIR, 0777, true);
            }
        }
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

    public function testDefinirImagem()
    {
        $imagemBase64 = 'data:image/jpeg;base64,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';

        $imagem = new \Domain\Entity\Imagem();
        $resposta = Formulario::setImagem($imagem, $imagemBase64);

        print_r($imagem);
        print_r($resposta);

        if (file_exists($resposta->getArquivoTemporario())) {
            unlink($resposta->getArquivoTemporario());
        }
    }

}
