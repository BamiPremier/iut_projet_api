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
}
