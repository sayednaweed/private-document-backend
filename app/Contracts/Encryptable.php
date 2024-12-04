<?php

// app/Contracts/Encryptable.php
namespace App\Contracts;

interface Encryptable
{
    public static function getEncryptedFields(): array;
}
