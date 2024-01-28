<?php

namespace App\DataFixtures;

use App\Entity\Product;
use App\Entity\Rank;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;

class RankFixtures extends Fixture
{
  private $ranks = [
    'iron' => ['name' => 'Fer', 'required_points' => 0],
    'bronze' => ['name' => 'Bronze', 'required_points' => 25],
    'silver' => ['name' => 'Argent', 'required_points' => 50],
    'gold'   => ['name' => 'Or', 'required_points' => 100],
    'platinium' => ['name' => 'Platine', 'required_points' => 200],
    'diamond' => ['name' => 'Diamant', 'required_points' => 400],
  ];

  private function createRank(string $name, int $requiredPoints): Rank
  {
    $rank = new Rank();
    $rank->setName($name);
    $rank->setRequiredPoint($requiredPoints);

    return $rank;
  }

  public function load(ObjectManager $manager)
  {
    foreach ($this->ranks as $rankValue) {
      $rank = $this->createRank($rankValue['name'], $rankValue['required_points']);
      $manager->persist($rank);
    }

    $manager->flush();
  }
}
