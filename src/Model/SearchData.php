<?php

namespace App\Model;
use App\Entity\Tag;

class SearchData{
    public ?string $q = null;
    public array $tags = [];
}