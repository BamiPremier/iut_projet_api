<?php

namespace App\Entity;

use App\Repository\ReservationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReservationRepository::class)]
class Reservation
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;
 

    #[ORM\ManyToOne(inversedBy: 'reservations')]
    private ?UserPlateform $utilisateur = null;

    #[ORM\ManyToOne(inversedBy: 'reservations')]
    private ?Salle $sale = null;

    #[ORM\Column(length: 255, nullable:true)]
    private ?string $debut = null;

    #[ORM\Column(length:255, nullable: true)]
    private ?string $fin = null;

    #[ORM\Column(length:255, nullable: true)]
    private ?string $motif = null;

    public function getId(): ?int
    {
        return $this->id;
    }
 

    public function getUtilisateur(): ?UserPlateform
    {
        return $this->utilisateur;
    }

    public function setUtilisateur(?UserPlateform $utilisateur): self
    {
        $this->utilisateur = $utilisateur;

        return $this;
    }

    public function getSale(): ?Salle
    {
        return $this->sale;
    }

    public function setSale(?Salle $sale): self
    {
        $this->sale = $sale;

        return $this;
    }

    public function getDebut(): ?string
    {
        return $this->debut;
    }

    public function setDebut(string $debut): self
    {
        $this->debut = $debut;

        return $this;
    }

    public function getFin(): ?string
    {
        return $this->fin;
    }

    public function setFin(string $fin): self
    {
        $this->fin = $fin;

        return $this;
    }

    public function getMotif(): ?string
    {
        return $this->motif;
    }

    public function setMotif(string $motif): self
    {
        $this->motif = $motif;

        return $this;
    }
}
