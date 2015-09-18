<?php

namespace ApiBundle;

use ApiBundle\DependencyInjection\ApiExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class ApiBundle extends Bundle
{
    public function getContainerExtension()
    {
        return new ApiExtension();
    }
}
