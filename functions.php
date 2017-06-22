<?php

function isValid($google_recaptcha_client_side_response) {
    try {
        $url = GOOGLE_RECAPTCHA_VERIFY_API;
        $data = ['secret'   => GOOGLE_RECAPTCHA_SERVER_SIDE_SECRET,
                 'response' => $google_recaptcha_client_side_response,
                 'remoteip' => getUserIP()];

        $options = [
            'http' => [
                'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                'method'  => 'POST',
                'content' => http_build_query($data)
            ]
        ];

        $context  = stream_context_create($options);
        $result = file_get_contents($url, false, $context);

        return json_decode($result) -> success;
    }
    catch (Exception $e) {
        return null;
    }
}

// Get the user IP address
function getUserIP() {
    $ipaddress = '';

    if (isset($_SERVER['HTTP_CLIENT_IP'])) {
        $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
    }
    else if(isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
    }
    else if(isset($_SERVER['HTTP_X_FORWARDED'])) {
        $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
    }
    else if(isset($_SERVER['HTTP_X_CLUSTER_CLIENT_IP'])) {
        $ipaddress = $_SERVER['HTTP_X_CLUSTER_CLIENT_IP'];
    }
    else if(isset($_SERVER['HTTP_FORWARDED_FOR'])) {
        $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
    }
    else if(isset($_SERVER['HTTP_FORWARDED'])) {
        $ipaddress = $_SERVER['HTTP_FORWARDED'];
    }
    else if(isset($_SERVER['REMOTE_ADDR'])) {
        $ipaddress = $_SERVER['REMOTE_ADDR'];
    }
    else {
        $ipaddress = 'UNKNOWN';
    }

    return $ipaddress;
}

function sendEmail($mailto, $subject, $message, $single_attachment, $file_name) {
    $content = file_get_contents($single_attachment);
    $content = chunk_split(base64_encode($content));

    // A random hash will be necessary to send mixed content
    $separator = md5(time());

    // Carriage return type (RFC)
    $eol = "\r\n";

    // Main header (multipart mandatory)
    $headers = "From: name <question@lepodcastagile.fr>" . $eol;
    $headers .= "MIME-Version: 1.0" . $eol;
    $headers .= "Content-Type: multipart/mixed; boundary=\"" . $separator . "\"" . $eol;
    $headers .= "Content-Transfer-Encoding: 7bit" . $eol;
    $headers .= "This is a MIME encoded message." . $eol;

    // Message
    $body = "--" . $separator . $eol;
    $body .= "Content-Type: text/plain; charset=\"iso-8859-1\"" . $eol;
    $body .= "Content-Transfer-Encoding: 8bit" . $eol;
    $body .= $message . $eol;

    // Attachment
    $body .= "--" . $separator . $eol;
    $body .= "Content-Type: application/octet-stream; name=\"" . $file_name . "\"" . $eol;
    $body .= "Content-Transfer-Encoding: base64" . $eol;
    $body .= "Content-Disposition: attachment" . $eol;
    $body .= $content . $eol;
    $body .= "--" . $separator . "--";

    // Send the email
    if (mail($mailto, $subject, $body, $headers)) {
        $callback_message .= logInfo("Email send... OK");
    } else {
        $callback_message .= logInfo("Email send... ERROR");
    }
}

function logInfo($message) {
    $file = "voicemail_log.txt";

    // The new log to add to the file at the current time
    $log = date("Y-m-d_H-i-s") . ": " . $message . "\r\n";

    // Write the contents to the file,
    // using the FILE_APPEND flag to append the content to the end of the file
    // and the LOCK_EX flag to prevent anyone else writing to the file at the same time
    file_put_contents($file, $log, FILE_APPEND | LOCK_EX);

    return $message . "; ";
}

?>
