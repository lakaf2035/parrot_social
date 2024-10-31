<?php

namespace local_parrot_social\Models;

defined('MOODLE_INTERNAL') || die;

class Friendship extends Base {
    protected static $table = 'parrot_social_friends';
    protected static $primary = 'id';

    public function getAttributes() {
        return array(
            'id' => NULL,
            'parrot1_id' => NULL,
            'parrot2_id' => NULL,
            'created_at' => function () {
                return time();
            },
        );
    }
}
