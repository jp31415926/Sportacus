<?php

namespace Scheduler\SchBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr;

/**
 * AgeGroupRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class AgeGroupRepository extends EntityRepository {

  public function findAllOrderedByName() {
    return $this->getEntityManager()
                    ->createQuery('SELECT p FROM SchedulerBundle:AgeGroup p ORDER BY p.name ASC')
                    ->getResult();
  }

  public function findAllByProject($project) {
    $qb = $this->getEntityManager()->createQueryBuilder();
    $qb->select('ag')
            ->from('SchedulerBundle:AgeGroup', 'ag')
            ->leftJoin('ag.region', 'r', Expr\Join::WITH) // Doctine figures out 'ON ag.region = r.id'
    ;
    if ($project) {
      $qb->where('ag.project = ?2')
              ->setParameter(2, $project->getId());
    }
    $qb->orderBy('ag.region', 'ASC')
            ->addOrderBy('ag.difficulty', 'ASC');

    return $qb->getQuery()->getResult();

//    $q = 'SELECT t FROM SchedulerBundle:AgeGroup t';
//    if ($project) {
//      $projectid = $project->getId();
//      $q .= " WHERE t.project=$projectid";
//    }
//    $q .= ' ORDER BY t.region ASC, t.difficulty ASC';
//    return $this->getEntityManager()
//      ->createQuery($q)
//      ->getResult();
  }

  public function findAllByProjectAndRegion($project, $region) {
    if ($project && $region) {
      $projectid = $project->getId();
      $regionid = $region->getId();
      $q = 'SELECT t FROM SchedulerBundle:AgeGroup t' .
              " WHERE t.project=$projectid" .
              " AND t.region=$regionid" .
              ' ORDER BY t.difficulty ASC';
      return $this->getEntityManager()
                      ->createQuery($q)
                      ->getResult();
    } else {
      return array();
    }
  }

  public function findAllByProjectAndRegionAndName($project, $region, $name) {
    if ($project && $region) {
      $projectid = $project->getId();
      $regionid = $region->getId();
      $q = 'SELECT t FROM SchedulerBundle:AgeGroup t' .
              " WHERE t.project=$projectid" .
              " AND t.region=$regionid" .
              " AND t.name=$name";
      return $this->getEntityManager()
                      ->createQuery($q)
                      ->getResult();
    } else {
      return array();
    }
  }

}