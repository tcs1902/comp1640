<?php

namespace App\Entity;

interface SoftDeletableInterface
{
    /**
     * @return bool
     */
    public function isDeleted();

    /**
     * @param bool $deleted
     */
    public function setDeleted(bool $deleted);
}
