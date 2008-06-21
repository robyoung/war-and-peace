<?php

class Classifier
{
  private $edge_type_tags;
  public static function create()
  {
    $classifier = new self();
    $classifier->setEdgeTypeTags(dbSelect("SELECT * FROM edge_type_tags ORDER BY sort_order ASC"));
    return $classifier;
  }

  public function setEdgeTypeTags($edge_type_tags)
  {
    $this->edge_type_tags = array();
    $this->tags           = array();
    foreach ($edge_type_tags as $edge_type_tag) {
      $this->edge_type_tags[$edge_type_tag['tag']] = $edge_type_tag;
    }
  }

  public function getEdgeType($text)
  {
    foreach ($this->edge_type_tags as $tag => $info) {
      if (preg_match('/' . $tag . '/i', $text)) {
        return $info;
      }
    }
  }
}
