<?php

class CountryFinder
{
  private $country_tags;
  private $tags;

  public static function create()
  {
    $finder = new self();
    $finder->setCountryTags(dbSelect("SELECT * FROM countries JOIN country_tags ON (countries.id=country_tags.country_id)"));
    return $finder;
  }

  public function setCountryTags($country_tags)
  {
    $this->country_tags = array();
    $this->tags = array();
    foreach ($country_tags as $country_tag) {
      $this->country_tags[$country_tag['tag']] = $country_tag;
      $this->tags[] = $country_tag['tag'];
    }
  }

  public function getLocations($ngrams)
  {
    $matches = array();
    foreach ($ngrams as $ngram) {
      $i = array_search($ngram, $this->tags);
      if ($i!==false && !in_array($ngram, $matches)) {
        $matches[] = $ngram;
      }
    }
    $return = array();
    $ids = array();
    foreach ($matches as $ngram) {
      $country = $this->country_tags[$ngram];
      if (!in_array($country['country_id'], $ids)) {
        $return[] = $country;
        $ids[] = $country['country_id'];
      }
    }
    return $return;
  }
}
