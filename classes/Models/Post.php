<?php

namespace local_parrot_social\Models;

defined('MOODLE_INTERNAL') || die;

class Post extends Base {
    protected static $table = 'parrot_social_posts';
    protected static $primary = 'id';


    public function likes() {
        return Like::all(['post_id' => $this->id]);
    }

    public function getAttributes() {
        return array(
            'id' => NULL,
            'parrot_id' => NULL,
            'title' => NULL,
            'text' => '',
            'created_at' =>  function () {
                return time();
            },
        );
    }
}
