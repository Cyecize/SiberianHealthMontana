<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Township
 *
 * @ORM\Table(name="townships")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\TownshipRepository")
 */
class Township
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="town_name", type="string", length=45, unique=true)
     */
    private $townName;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\UserAddress", mappedBy="townShip")
     */
    private $addresses;


    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set townName
     *
     * @param string $townName
     *
     * @return Township
     */
    public function setTownName($townName)
    {
        $this->townName = $townName;

        return $this;
    }

    /**
     * Get townName
     *
     * @return string
     */
    public function getTownName()
    {
        return $this->townName;
    }
}

