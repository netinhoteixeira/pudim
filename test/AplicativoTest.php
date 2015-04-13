<?php

require_once implode(DIRECTORY_SEPARATOR, [__DIR__, '..', 'source', 'nucleo', 'lib', 'Pudim', 'Arquivo.php']);

class AplicativoTest extends PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        require_once '../vendor/autoload.php';
        \Pudim\Arquivo::requererDiretorio(implode(DIRECTORY_SEPARATOR, [__DIR__, '..', 'source']));
    }

    public function testEfetuarConexao()
    {
        $aplicativo = \Pudim\Aplicativo::getInstance(__DIR__ . DIRECTORY_SEPARATOR .'../');

        // cadastra um colaborador
        $colaborador = new \Domain\Entity\Colaborador();

        $colaborador->setCadastro(new \DateTime());
        $colaborador->setNome('Joao');
        $colaborador->setNascimento(new \DateTime());
        $colaborador->setSexo('M');
        $colaborador->setCpf('05486864401');
        $colaborador->setEstadocivil('S');
        $colaborador->setPerfil('F');
        $colaborador->setAtivo(true);

        try {
            $aplicativo->getConexao()->persist($colaborador);
            $aplicativo->getConexao()->flush();

            echo 'Cadastrado com sucesso!';
        } catch (Exception $ex) {
            echo $ex->getMessage();
        }
    }

}
