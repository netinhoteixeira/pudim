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

use Pudim\Respostas\RespostaMudancaFoto;

class Formulario
{

    /**
     * Define a foto do $documento.
     * 
     * @param type $documento Documento
     * @param type $variavel Foto em Base64
     * @return \RespostaMudancaFoto
     */
    public static function setFoto($documento, $variavel = 'foto', $redimensionar = true)
    {
        $aplicativo = Aplicativo::getInstance();

        $resposta = new RespostaMudancaFoto();

        if ($aplicativo->postExists($variavel, false)) {
            // sanitiza os dados da foto recebida na $variavel
            $prefix = 'data:image/png;base64,';
            $data = str_replace($prefix, '', $aplicativo->post($variavel));

            // obtém a foto do $documento
            $foto = Formulario::getFotoBase64($documento);

            // processa a imagem somente se for diferente
            if ($data !== $foto) {
                $data = base64_decode($data);
                $imagemRecebida = imagecreatefromstring($data);

                if ($redimensionar) {
                    $layer = Formulario::imageLayerFromResource($imagemRecebida);
                    $layer->resizeInPixel(200, 200, true, 0, 0, 'MM');
                    $imagemFinal = $layer->getResult('FFFFFF');
                } else {
                    $imagemFinal = $imagemRecebida;
                }

                $arquivoTemporario = tempnam(TMPDIR, 'foto');
                imagepng($imagemFinal, $arquivoTemporario);

                if (is_null($documento->getFoto())) {
                    $foto = new foto();
                    $foto->setFilename(basename($arquivoTemporario));
                    $foto->setFile($arquivoTemporario);
                    $documento->setFoto($foto);
                } else {
                    $documento->getFoto()->setFile($arquivoTemporario);
                }

                imagedestroy($imagemFinal);

                $resposta->setMudou(true);
                $resposta->setArquivoTemporario($arquivoTemporario);
            }
        }

        return $resposta;
    }

    /**
     * Extrai a foto do sistema codificado com Bas64. Caso seja como imagem
     * estará pronto para inserir na tag img.
     * 
     * @param type $documento Documento
     * @param type $asImage Se pronto para imagem
     * @return string
     */
    public static function getFotoBase64($documento, $asImage = false)
    {
        $retorno = null;

        try {
            if (!is_null($documento->getFoto())) {
                if (!is_null($documento->getFoto()->getFile())) {
                    if (!is_null($documento->getFoto()->getFile()->getBytes())) {
                        $foto = $documento->getFoto();
                        $arquivo = base64_encode($foto->getFile()->getBytes());
                        if ($asImage) {
                            $retorno = 'data:image/png;base64,' . $arquivo;
                        } else {
                            $retorno = $arquivo;
                        }
                    }
                }
            }
        } catch (MongoGridFSException $ex) {
            // ignora caso haja erro
        }

        return $retorno;
    }

    private static function imageLayerFromResource($image)
    {
        return PHPImageWorkshop\ImageWorkshop::initFromResourceVar($image);
    }

    /**
     * Trata as mensagens de erro do MongoCursorException
     * 
     * @param \MongoCursorException $exception
     * @return string Mensagem tratada
     */
    public static function tratarMongoCursorException($exception)
    {
        $continuar = true;
        $mensagemErroIndice = 'E11000 duplicate key error index';
        $mensagem = '';

        if (strpos($exception->getMessage(), $mensagemErroIndice) !== false) {

            if ((strpos($exception->getMessage(), '$nome') !== false)) {
                $continuar = false;
                $mensagem = 'O nome fornecido já está cadastrado.';
            } elseif ((strpos($exception->getMessage(), '$cnpj') !== false)) {
                $continuar = false;
                $mensagem = 'O cnpj fornecido já está cadastrado.';
            } elseif ((strpos($exception->getMessage(), '$email') !== false)) {
                $continuar = false;
                $mensagem = 'O email fornecido já está cadastrado.';
            }
        }

        if ($continuar) {
            $mensagem = $exception->getMessage() .
                    ' (' . $exception->getCode() . ')';
        }

        return $mensagem;
    }

    /**
     * Valida o e-mail fornecido.
     *
     * @param type $email E-mail a ser validado
     * @return boolean Se é um e-mail é ou não válido
     */
    public static function validarEmail($email)
    {
        $regexEmail = '/^([a-zA-Z0-9._-])*([@])([a-z0-9]).([a-z]{2,3})/';

        // verifica se e-mail esta no formato correto de escrita
        if (!preg_match($regexEmail, $email)) {
            return false;
        } elseif (!PROJECT_STAGE) { // pode estar offline no desenvolvimento local
            return true;
        } else {
            // valida o domínio
            $dominio = explode('@', $email);
            if (!checkdnsrr($dominio[1], 'A')) {
                return false;
            } else {
                return true;
            }
        }
    }

    /**
     * 
     * @param type $cpf
     * @return type
     */
    public static function validarCpf($cpf)
    {
        require_once(__DIR__ . '/../../lib/ValidarChaveFiscal.php');
        $validador = new \ValidarChaveFiscal($cpf, 'cpf');
        return $validador->isValido();
    }

    /**
     * 
     * @param type $cnpj
     * @return type
     */
    public static function validarCnpj($cnpj)
    {
        require_once(__DIR__ . '/../../lib/ValidarChaveFiscal.php');
        $validador = new \ValidarChaveFiscal($cnpj, 'cnpj');
        return $validador->isValido();
    }

}
