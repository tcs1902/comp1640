<?php

namespace App\Entity;

interface TimestampableInterface
{
    /**
     * @return \DateTimeInterface
     */
    public function getCreatedAt(): ?\DateTimeInterface;

    /**
     * @param \DateTimeInterface|null $createdAt
     *
     * @return $this
     */
    public function setCreatedAt(?\DateTimeInterface $createdAt);

    /**
     * @return \DateTimeInterface
     */
    public function getUpdatedAt(): ?\DateTimeInterface;

    /**
     * @param \DateTimeInterface|null $updatedAt
     *
     * @return $this
     */
    public function setUpdatedAt(?\DateTimeInterface $updatedAt);
}
