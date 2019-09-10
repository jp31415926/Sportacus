<?php

namespace Scheduler\SchBundle\Twig;

class TwigExtensions extends \Twig_Extension
{
  public function getFilters()
  {
    return array(
      new \Twig_SimpleFilter('phone', array($this, 'phoneFilter')),
      new \Twig_SimpleFilter('csvQuote', array($this, 'csvQuoteFilter')),
    );
  }

  public function phoneFilter($number)
  {
    $number = preg_replace('/[^0-9]/', '', $number);
    if (strlen($number) <= 4) {
      $phone = $number;
    } else if (strlen($number) <= 7) {
      $phone = substr($number, -7, strlen($number) - 4) . '-' . substr($number, -4);
    } else if (strlen($number) <= 10) {
      $phone = '(' . substr($number, -10, strlen($number) - 7) . ') ' . substr($number, -7, 3) . '-' . substr($number, -4);
    } else {
      $phone = substr($number, 0, strlen($number) - 10) . ' (' . substr($number, -10, 3) . ') ' . substr($number, -7, 3) . '-' . substr($number, -4);
    }

    return $phone;
  }

  public function csvQuoteFilter($s)
  {
    if ((strpos($s, '"') !== FALSE) ||
      (strpos($s, ',') !== FALSE) ||
      (strpos($s, '\'') !== FALSE)
    )
      return '"' . addslashes($s) . '"';
    else
      return $s;
  }

  public function getName()
  {
    return 'SchBundle_twig_extension';
  }
}
