<?php

namespace App\Repository;

use App\Entity\Client;
use App\Entity\Location;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Location>
 *
 * @method Location|null find($id, $lockMode = null, $lockVersion = null)
 * @method Location|null findOneBy(array $criteria, array $orderBy = null)
 * @method Location[]    findAll()
 * @method Location[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LocationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Location::class);
    }

    public function save(Location $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Location $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return Location[] Returns an array of active Location objects
     */
    public function findActiveLocations(): array
    {
        $now = new \DateTime();
        
        return $this->createQueryBuilder('l')
            ->andWhere('l.dateDebut <= :now')
            ->andWhere('l.dateFin >= :now')
            ->setParameter('now', $now)
            ->orderBy('l.dateFin', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Location[] Returns an array of Location objects for a client
     */
    public function findByClient(Client $client): array
    {
        return $this->createQueryBuilder('l')
            ->andWhere('l.client = :client')
            ->setParameter('client', $client)
            ->orderBy('l.dateDebut', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Vérifie si un vélo est disponible pour une période donnée
     */
    public function isVeloAvailableForPeriod(int $veloId, \DateTime $start, \DateTime $end): bool
    {
        $conflictingLocations = $this->createQueryBuilder('l')
            ->andWhere('l.velo = :veloId')
            ->andWhere('
                (l.dateDebut <= :end AND l.dateFin >= :start)
            ')
            ->setParameter('veloId', $veloId)
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->getQuery()
            ->getResult();

        return count($conflictingLocations) === 0;
    }
} 