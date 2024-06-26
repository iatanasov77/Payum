<?php

namespace Payum\Core\Bridge\Symfony;

use Payum\Core\Registry\AbstractRegistry;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

/**
 * @template T of object
 * @extends AbstractRegistry<T>
 */
class ContainerAwareRegistry extends AbstractRegistry implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    protected function getService($id): ?object
    {
        return $this->container->get($id);
    }
}
