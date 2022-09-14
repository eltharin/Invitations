<?php

namespace Eltharin\InvitationsBundle\Repository;

use Eltharin\InvitationsBundle\Entity\Invitation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Eltharin\InvitationsBundle\Exception\AlreadyExistsException;
use Eltharin\InvitationsBundle\Exception\CantCreateInvitationException;
use Eltharin\InvitationsBundle\Service\InvitationEntityManager;

/**
 * @extends ServiceEntityRepository<Invitation>
 *
 * @method Invitation|null find($id, $lockMode = null, $lockVersion = null)
 * @method Invitation|null findOneBy(array $criteria, array $orderBy = null)
 * @method Invitation[]    findAll()
 * @method Invitation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class InvitationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Invitation::class);
   }

    public function add(Invitation $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Invitation $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
