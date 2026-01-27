<?php

namespace App\Enum;

/**
 * Type d'affectation d'un matériel à une ligne de contrat
 */
enum TypeAffectation: string
{
    case PRINCIPALE = 'PRINCIPALE';
    case REMPLACEMENT_TEMP = 'REMPLACEMENT_TEMP';
    case REMPLACEMENT_DEF = 'REMPLACEMENT_DEF';
    case PRET = 'PRET';
}
