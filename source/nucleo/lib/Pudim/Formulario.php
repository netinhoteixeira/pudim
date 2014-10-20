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

use Pudim\Respostas\RespostaMudancaImagem;

class Formulario
{

    /**
     * Define a imagem de $imagem.
     *
     * @param \Domain\Entity\Imagem $imagem Imagem
     * @param string $imagemBase64 Imagem em Base64
     * @return \RespostaMudancaImagem
     */
    public static function setImagem(&$imagem, $imagemBase64, $redimensionar = false)
    {
        $resposta = new RespostaMudancaImagem();

        Formulario::imagemSanitizarPrefixo($imagemBase64);

        // processa a imagem somente se for diferente
        if ($imagemBase64 !== Formulario::getImagemBase64($imagem)) {

            $arquivoTemporario = Formulario::imagemCriarArquivoTemporario($imagem, $imagemBase64, $redimensionar);

            $imagem->setFile($arquivoTemporario);

            $resposta->setMudou(true);
            $resposta->setArquivoTemporario($arquivoTemporario);
        }

        return $resposta;
    }

    /**
     * Sanitiza os dados da imagem recebida em $imagemBase64
     */
    private static function imagemSanitizarPrefixo(&$imagemBase64)
    {
        $prefixes = ['png', 'jpeg'];
        foreach ($prefixes as $prefix) {
            $prefix = 'data:image/' . $prefix . ';base64,';

            if (strpos($imagemBase64, $prefix) !== false) {
                $imagemBase64 = str_replace($prefix, '', $imagemBase64);
            }
        }
    }

    /**
     * 
     * @param \Domain\Entity\Imagem $imagem
     * @param type $imagemBase64
     * @param type $redimensionar
     * @return type
     */
    private static function imagemCriarArquivoTemporario(&$imagem, $imagemBase64, $redimensionar)
    {
        $imagemFinal = Formulario::imagemRedimensionar(imagecreatefromstring(base64_decode($imagemBase64)), $redimensionar);

        $arquivoTemporario = tempnam(TMPDIR, 'imagem');

        // formato WEBP que utiliza menor tamanho e preserva a qualidade
        if (function_exists('imagewebp')) {
            imagewebp($imagemFinal, $arquivoTemporario);
            $mimeType = 'image/webp';
        } else {
            // do contrário coloca em JPEG mantendo a qualidade (muito grande)
            imagejpeg($imagemFinal, $arquivoTemporario, 100);
            $mimeType = 'image/jpeg';
        }

        if (is_null($imagem)) {
            $imagem = new \Domain\Entity\Imagem();
            $imagem->setFilename(basename($arquivoTemporario));
        }
        $imagem->setMimeType($mimeType);
        imagedestroy($imagemFinal);

        return $arquivoTemporario;
    }

    /**
     *
     * @param type $imagem
     * @param type $redimensionar
     * @return type
     */
    private static function imagemRedimensionar($imagem, $redimensionar)
    {
        // caso a imagem venha a ser redimensionada. Ex.: '200x200'
        if ($redimensionar) {
            $dimensoes = explode('x', $redimensionar);

            if (count($dimensoes) === 2) {
                $layer = Formulario::imagemExtrairCamadaDoRecurso($imagem);
                $layer->resizeInPixel($dimensoes[0], $dimensoes[1], true, 0, 0, 'MM');
                $imagemFinal = $layer->getResult('FFFFFF');
            }
        }

        // caso a imagem não tenha sido redimensionada, não existir
        if (!isset($imagemFinal)) {
            // define a $imagemRecebido para a $imagemFinal
            $imagemFinal = $imagem;
        }

        return $imagemFinal;
    }

    /**
     * Extrai a imagem do sistema codificado com Base64. Caso seja como imagem
     * estará pronto para inserir na tag img.
     *
     * @param \Domain\Entity\Imagem $imagem Imagem
     * @param boolean $comPrefixo Se pronto para imagem
     * @return string
     */
    public static function getImagemBase64($imagem, $comPrefixo = false)
    {
        $retorno = null;

        try {
            if ((!is_null($imagem)) && (!is_null($imagem->getFile())) && (!is_null($imagem->getFile()->getBytes()))) {
                $retorno = Formulario::getImagemBase64ComPrefixo($imagem->getFile()->getBytes(), $imagem->getMimeType(), $comPrefixo);
            }
        } catch (MongoGridFSException $ex) {
            error_log(json_encode($ex));
        }

        return $retorno;
    }

    /**
     * 
     * @param type $bytes
     * @param type $mimeType
     * @param type $comPrefixo
     * @return string
     */
    private static function getImagemBase64ComPrefixo($bytes, $mimeType, $comPrefixo = false)
    {
        $arquivo = base64_encode($bytes);

        if ($comPrefixo) {
            $retorno = 'data:' . $mimeType . ';base64,' . $arquivo;
        } else {
            $retorno = $arquivo;
        }

        return $retorno;
    }

    /**
     *
     * @param type $image
     * @return type
     */
    private static function imagemExtrairCamadaDoRecurso($image)
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
        require_once(__DIR__ . '/../../library/ValidarChaveFiscal.php');
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
        require_once(__DIR__ . '/../../library/ValidarChaveFiscal.php');
        $validador = new \ValidarChaveFiscal($cnpj, 'cnpj');
        return $validador->isValido();
    }

}
