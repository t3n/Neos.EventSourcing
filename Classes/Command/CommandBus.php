<?php
namespace Flowpack\Cqrs\Command;

/*
 * This file is part of the Flowpack.Cqrs package.
 *
 * (c) Hand crafted with love in each details by medialib.tv
 */

use Flowpack\Cqrs\Command\Exception\CommandBusException;
use Flowpack\Cqrs\Message\Resolver\ResolverInterface;
use TYPO3\Flow\Annotations as Flow;
use TYPO3\Flow\Object\ObjectManagerInterface;

/**
 * CommandBus
 *
 * @Flow\Scope("singleton")
 */
class CommandBus implements CommandBusInterface
{
    /**
     * @var ObjectManagerInterface
     * @Flow\Inject
     */
    protected $objectManager;

    /**
     * @var ResolverInterface
     * @Flow\Inject
     */
    protected $resolver;

    /**
     * @var array
     */
    protected $queue = [];

    /**
     * @var boolean
     */
    protected $isHandling = false;

    /**
     * @param CommandInterface $command
     * @return void
     * @todo Need some testing...
     */
    public function handle(CommandInterface $command)
    {
        $this->queue[] = $command;

        if ($this->isHandling) {
            return;
        }

        $this->isHandling = true;

        try {
            while ($command = array_shift($this->queue)) {
                $this->getHandler($command)->handle($command);
            }
        } finally {
            $this->isHandling = false;
        }
    }

    /**
     * @param CommandInterface $message
     * @return CommandHandlerInterface
     * @throws CommandBusException
     */
    protected function getHandler(CommandInterface $message)
    {
        $messageName = $message->getName();

        $handlerClassName = $this->resolver->resolve($messageName);

        if (!$this->objectManager->isRegistered($handlerClassName)) {
            throw new CommandBusException(
                sprintf(
                    "Cannot instantiate handler '%s' for command '%s'",
                    $handlerClassName,
                    $messageName
                )
            );
        }

        /** @var CommandHandlerInterface $handler */
        $handler = $this->objectManager->get($handlerClassName);

        if (!$handler instanceof CommandHandlerInterface) {
            throw new CommandBusException(
                sprintf(
                    "Handler '%s' returned by locator for command '%s' should implement CommandHandlerInterface",
                    $handlerClassName,
                    $messageName
                )
            );
        }

        return $handler;
    }
}