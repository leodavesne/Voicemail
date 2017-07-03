<?php

$_GLOBALS["callback_message"] = "Reply from the server: ";

$is_secured = false;

include_once('settings.php');
include_once('functions.php');

if (!isset($_POST["g-recaptcha-response"])) {
    $_GLOBALS["callback_message"] .= logInfo("reCAPTCHA is empy");
}
else if (!isValid($_POST["g-recaptcha-response"])) {
    $_GLOBALS["callback_message"] .= logInfo("reCAPTCHA response is wrong");
}
else {
    $is_secured = true;
}

if ($is_secured) {
    if (isset($_FILES["question"]) and !$_FILES["question"]["error"]) {
        $current_time = date("Y-m-d_H-i-s");
        $_GLOBALS["callback_message"] .= logInfo("Question received");
        $voice_message_name = "Voicemail_" . $current_time . "_" . rand(1, 1000000) . ".wav";

        try {
            move_uploaded_file($_FILES["question"]["tmp_name"], "wav/" . $voice_message_name);

            $_GLOBALS["callback_message"] .= logInfo($voice_message_name . " properly saved");
        } catch (Exception $e) {
            $_GLOBALS["callback_message"] .= logInfo("ERROR:" . $e);
        }

        try {
            $name = $_POST["voicemail_name"];
            /*
            // Email validation, really necessary?
            if (isset($_POST["voicemail_email"]) && !filter_var($_POST["voicemail_email"], FILTER_VALIDATE_EMAIL)) {
                // Invalid emailaddress
            }
            */
            $email = $_POST["voicemail_email"];
            $origin = $_POST["voicemail_origin"];
            $newsletter = $_POST["voicemail_newsletter"];

            $filename = $voice_message_name;
            $path = "wav";
            $file = $path . "/" . $filename;

            $mailto = VOI_EMAIL_VOICEMAILS_TO;
            $subject = "New Voicemail by " . $name . " (" . $email . ")!";
            $message = $name . " (" . $email . ") let you a new voicemail: " . $voice_message_name . "!";
            $message .= "\r\nOrigin: " . $origin;
            $message .= "\r\nSubscribed to the newsletter: " . $newsletter;

            sendEmail($mailto, $subject, $message, $file, $voice_message_name);

            $_GLOBALS["callback_message"] .= logInfo($voice_message_name . " let by " . $name . " (" . $email . ")");
        } catch (Exception $e) {
            $_GLOBALS["callback_message"] .= logInfo("ERROR:" . $e);
        }
    }
    else {
        $_GLOBALS["callback_message"] .= logInfo("No voicemail received");
    }
}
else {
    $_GLOBALS["callback_message"] .= logInfo("NOT secured");
}

echo $_GLOBALS["callback_message"];

?>
