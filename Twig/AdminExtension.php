<?php

namespace CR\AdminBundle\Twig;

class AdminExtension extends \Twig_Extension
{
    public function getFunctions()
    {
        return array(
            'isDateTime' => new \Twig_Function_Method($this, 'isDateTime'),
        );
    }

    /**
     * @param $date
     * @return bool
     */
    public function isDateTime($date)
    {
        return $date instanceof \DateTime;
    }

    public function getName()
    {
        return 'cr_admin_extension';
    }
}
