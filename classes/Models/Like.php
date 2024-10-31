<?php

namespace local_parrot_social\Models;

defined('MOODLE_INTERNAL') || die;

class Like extends Base {
    protected static $table = 'parrot_social_likes';
    protected static $primary = 'id';

    public function getAttributes() {
        return array(
            'id' => NULL,
            'parrot_id' => NULL,
            'post_id' => NULL,
            'created_at' =>  function () {
                return time();
            },
        );
    }
}
