<?php

namespace App\Twig;

use App\Repository\RankRepository;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class AppExtension extends AbstractExtension
{
  private $rankRepository;

  public function __construct(RankRepository $rankRepository)
  {
    $this->rankRepository = $rankRepository;
  }

  public function getFilters()
  {
    return [
      new TwigFilter('truncate', [$this, 'formatText']),
      new TwigFilter('getRank', [$this, 'getRank']),
    ];
  }

  public function formatText($text)
  {
    if (strlen($text) <= 60) {
      return $text;
    }

    $truncatedText = substr($text, 0, 60) . "...";
    return $truncatedText;
  }

  public function getRank($activityPoint)
  {
    $rank = $this->rankRepository->findCurrentRank($activityPoint);
    return $rank->getName();
  }
}
