<?php

namespace local_parrot_social\Utils;

use core_user;

class Notification {
    /**
     * Send notification to a user
     */
    public static function send($notification, $user, $subject, $content, $url, $urlname, $smallmessage = '') {
        $message = new \core\message\message();
        $message->component = 'local_parrot_social'; // Your plugin's name
        $message->name = $notification; // Your notification name from message.php
        $message->userfrom = core_user::get_noreply_user(); // If the message is 'from' a specific user you can set them here
        $message->userto = $user;
        $message->subject = $subject;
        $message->fullmessage = $content;
        $message->fullmessageformat = FORMAT_HTML;
        $message->fullmessagehtml = $content;
        $message->smallmessage = $smallmessage;
        $message->notification = 1; // Because this is a notification generated from Moodle, not a user-to-user message
        $message->contexturl = $url; // A relevant URL for the notification
        $message->contexturlname = $urlname; // Link title explaining where users get to for the contexturl
        // $content = array('*' => array('header' => ' test ', 'footer' => ' test ')); // Extra content for specific processor
        // $message->set_additional_content('email', $content);
        return message_send($message);
    }
}
