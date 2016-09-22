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

/**
 * Situações da Encomenda.
 */
class CorreiosSituacoesEncomenda
{

    public static $cor = [
        'POSTADO DEPOIS' => '#e4e490',
        'POSTADO' => '#ffff9f', // acceptance
        'SAIU PARA ENTREGA' => '#ddeffc', // delivering
        'ENTREGA EFETUADA' => '#e8f5b6', // delivered
        'ENCAMINHADO' => '#fff8bb', // enroute
        'DESCONHECIDO' => '#f4e5ff' // unknown
    ];
    
    public static $codigo = [
        'POSTADO DEPOIS' => 'PD',
        'POSTADO' => 'PT', // acceptance
        'SAIU PARA ENTREGA' => 'SE', // delivering
        'ENTREGA EFETUADA' => 'EE', // delivered
        'ENCAMINHADO' => 'EC', // enroute
        'DESCONHECIDO' => 'DS' // unknown
    ];

    /*
      } else if (data[i].status === 'checked') {
      div.css('background-color', '#fffcd1');
      } else if (data[i].status === 'awaiting') {
      div.css('background-color', '#fff0e0');
     */
}
