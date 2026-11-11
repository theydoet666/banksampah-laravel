<?php

namespace App\Exceptions;

use Exception;

class InsufficientBalanceException extends Exception
{
    protected $message = 'Saldo nasabah tidak mencukupi untuk melakukan penarikan ini.';
}

