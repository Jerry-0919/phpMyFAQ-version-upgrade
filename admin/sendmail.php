<?php
    $to      = $_POST['mailto'];
    $subject = $_POST['question']."(Author : ".$_POST['author'].", Sender : faq.tibiona.it)"; 
    $message = $_POST['answer']; 
    $headers = "From: ".$_POST['senderName']." <".$_POST['email'].">\r\n"; $headers = "Reply-To: ".$_POST['email']."\r\n"; 
    $headers = "Content-type: text/html; charset=iso-8859-1\r\n";
    'X-Mailer: PHP/' . phpversion();
    //check if the mail was sent
    if (mail($to, $subject, $message, $headers)) {
        echo json_encode(['result' => true]);
    }else {
        echo json_encode(['result' => false]);
     }
?>