<?php

$select="SELECT id
from users
WHERE email = :email LIMIT 1";
$stmt=$this->conn->prepare($select);

$stmt->execute(array("email" => $emailtos));
$res = $stmt->fetchAll();
if(sizeof($res) < 1){
  $insert="INSERT into users(email,emailv) values(:email,0)";
  $stmt2=$this->conn->prepare($insert);
  $stmt2->execute(array("email"=>$emailtos));
  $id = $this->conn->lastInsertId();
} else {
  $id = $res[0]['id'];
}
$select="SELECT code, email
from emailverify
WHERE email = :email AND time_added > NOW() - INTERVAL 24 HOUR LIMIT 1";
$stmt=$this->conn->prepare($select);
$stmt->execute(array("email" => $emailtos));
$res = $stmt->fetchAll();
if(sizeof($res) < 1){
  $code = $this->uniqueid("alnum",16,false);
  $insert="INSERT into emailverify(code,email) values(:code,:email)";
  $stmt=$this->conn->prepare($insert);
  $stmt->execute(array("code"=>$code,"email"=>$emailtos));
  $this->outputPHP["codenew"] = true;
  $linkid = $this->conn->lastInsertId();
  $this->outputPHP["lastinserted"] = $linkid;
} else {
  $code = $res[0]["code"];
  $stmt = $this->conn->prepare("UPDATE emailverify SET verified=0,time_added = NOW() WHERE code=:code");
  $stmt->execute(array("code"=>$code));
  $this->outputPHP["codenew"] = false;
}

$boundary = "--".$this->uniqueid("alnum",32,false)."--";
$to      = $emailtos;
$subject = "Magic Code Request";
$headers = "From: ATcookpad <support>" . "\r\n" .
    "X-Mailer: PHP/" . phpversion()."\r\n";

$campaignid = new DateTime();
$campaignid = $campaignid->getTimestamp();
$headers .= "MIME-Version: 1.0\r\n";
$headers .= "Feedback-ID: ".$campaignid.$id.":".$id.":magiccode:magic\r\n";
$headers .= "Content-Type: multipart/alternative; boundary=".$boundary."\r\n\r\n";
$message = "This is a MIME encoded message.";
//text
$message .= "\r\n\r\n--" . $boundary . "\r\n";
$message .= "Content-Type: text/plain;charset=utf-8\r\n\r\n";
$text = "Hello!
You asked us to send you a magic code for quickly signingin to Simple-Insta.

Copy/paste this magic code where asked: ".$code."
Note: Your magic link will expire in 24 hours, and can only be used one time.";
$message .= $text;
$message .= "\r\n\r\n--" . $boundary . "\r\n";
$message .= 'Content-Type: text/html charset=utf-8'."\r\n";
//Html body
$html = '<p><span class="im" style="color: rgb(80, 0, 80); font-family: arial, sans-serif; font-size: 12.8px;">You asked us to send you a magic code for signingin to Simple-Insta.</span></p>

<p style="color: rgb(34, 34, 34); font-family: arial, sans-serif; font-size: 12.8px;"><span class="im" style="color: rgb(80, 0, 80);">Copy/paste this magic code where asked:&nbsp;<strong>'.$code.'</strong><br />
Note: Your magic link will expire in 24 hours, and can only be used one time.</span></p>';
$message .= $html;
$message .= "\r\n\r\n--" . $boundary . "--";
$this->outputPHP["emailsent"] = mail($to, $subject, $message, $headers);
?>
