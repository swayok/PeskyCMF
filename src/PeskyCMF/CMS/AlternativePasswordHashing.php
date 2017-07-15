<?php

namespace PeskyCMF\CMS;

interface AlternativePasswordHashing {

    /**
     * Hash password
     * Note: do not forget to use this method in value preprocessor of password column in your TableStructure class
     * @param string $plainPassword
     * @return string - hashed password
     */
    static public function hashPassword($plainPassword);

    /**
     * Check if $plainPassword is same as user's password
     * @param string $plainPassword
     * @return bool
     */
    public function checkPassword($plainPassword);
}