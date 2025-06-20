<?php

namespace App\Repository;

use App\Entity\Velo;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Velo>
 *
 * @method Velo|null find($id, $lockMode = null, $lockVersion = null)
 * @method Velo|null findOneBy(array $criteria, array $orderBy = null)
 * @method Velo[]    findAll()
 * @method Velo[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class VeloRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Velo::class);
    }

    public function save(Velo $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Velo $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return Velo[] Returns an array of available Velo objects
     */
    public function findAvailable(): array
    {
        return $this->createQueryBuilder('v')
            ->andWhere('v.etat = :etat')
            ->setParameter('etat', Velo::ETAT_DISPONIBLE)
            ->orderBy('v.id', 'ASC')
            ->getQuery()
            ->getResult();
    }
} 