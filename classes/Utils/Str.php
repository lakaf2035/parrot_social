<?php

namespace local_parrot_social\Utils;

class Str {
    /**
     * Convert 
     */
    public static function camelToSnake($input) {
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $input));
    }
}
