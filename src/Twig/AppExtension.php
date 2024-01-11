<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class AppExtension extends AbstractExtension
{
  public function getFilters()
  {
    return [
      new TwigFilter('truncate', [$this, 'formatText']),
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
}
