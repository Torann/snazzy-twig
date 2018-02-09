<?php

if (!function_exists('twig')) {
    /**
     * Get the evaluated twig contents for the given twig template.
     *
     * @param  string $source
     * @param  array  $data
     *
     * @return \Torann\SnazzyTwig\Environment|string
     */
    function twig($source = null, $data = [])
    {
        /** @var \Torann\SnazzyTwig\Environment $twig */
        $twig = app('twig');

        if (func_num_args() === 0) {
            return $twig;
        }

        return $twig->make($source, $data);
    }
}