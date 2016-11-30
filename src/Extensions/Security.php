<?php

namespace Torann\SnazzyTwig\Extensions;

use Twig_Extension_Sandbox;

class Security extends Twig_Extension_Sandbox
{
    public function __construct()
    {
        $this->policy = new Policies\SecurityPolicies;
        $this->sandboxedGlobally = true;
    }
}
