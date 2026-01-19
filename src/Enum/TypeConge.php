<?php
// src/Enum/TypeConge.php
namespace App\Enum;

enum TypeConge: string
{
    case PAYE = 'paye';
    case SANS_SOLDE = 'sans_solde';
    case MALADIE = 'maladie';
}
