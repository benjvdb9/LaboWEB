<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TestRepository")
 */
class Test
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $test;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $bertrand;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTest(): ?string
    {
        return $this->test;
    }

    public function setTest(?string $test): self
    {
        $this->test = $test;

        return $this;
    }

    public function getBertrand(): ?string
    {
        return $this->bertrand;
    }

    public function setBertrand(?string $bertrand): self
    {
        $this->bertrand = $bertrand;

        return $this;
    }
}
