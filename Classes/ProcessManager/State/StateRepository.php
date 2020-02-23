<?php
namespace Neos\EventSourcing\ProcessManager\State;

/*
 * This file is part of the Neos.EventSourcing package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Doctrine\ORM\EntityManagerInterface;
use Neos\Flow\Annotations as Flow;
use Psr\Log\LoggerInterface;

/**
 * A repository specialized on Process States
 *
 * @Flow\Scope("singleton")
 */
final class StateRepository
{
    /**
     * @Flow\Inject
     * @var LoggerInterface
     */
    protected $systemLogger;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @param EntityManagerInterface $entityManager
     * @return void
     */
    public function injectEntityManager(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param string $identifier
     * @param string $processManagerClassName
     * @return object|null
     */
    public function get(string $identifier, string $processManagerClassName)
    {
        return $this->entityManager->find(ProcessState::class, ['identifier' => $identifier, 'processManagerClassName' => $processManagerClassName]);
    }

    /**
     * @param ProcessState $state The State to save
     * @return void
     */
    public function save(ProcessState $state)
    {
        $this->entityManager->persist($state);
        $this->entityManager->flush();
    }

    /**
     * @param ProcessState $state
     */
    public function remove(ProcessState $state)
    {
        $this->entityManager->remove($state);
        $this->entityManager->flush();
    }
}
