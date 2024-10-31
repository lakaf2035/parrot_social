<?php

namespace local_parrot_social\Models;

defined('MOODLE_INTERNAL') || die;

class Invitation extends Base {
    const STATUS_PENDING = 'pending';
    const STATUS_ACCEPTED = 'accepted';
    const STATUS_REJECTED = 'rejected';

    protected static $table = 'parrot_social_invitations';
    protected static $primary = 'id';

    public function getAttributes() {
        return array(
            'id' => NULL,
            'recipient_parrot_id' => NULL,
            'sending_parrot_id' => NULL,
            'status' => self::STATUS_PENDING,
            'created_at' =>  function () {
                return time();
            },
        );
    }
}
