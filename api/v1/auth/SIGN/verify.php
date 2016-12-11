<?php
if(!isset($this->request['email']) || !isset($this->request['code']))
{
  $this->gotError(206);
  return false;
}

$email = $this->request['email'];
$code = $this->request['code'];
$stmt = $this->conn->prepare("SELECT * FROM emailverify WHERE email=:email AND code=:code LIMIT 1");
$stmt->execute(array(":email" => $email,":code" => $code));
$row=$stmt->fetchAll();
$time = strtotime($row[0]['time_added']);
$curtime = time();
if(sizeof($row) > 0) {
 if($row[0]['verified']){
   $this->outputPHP['verify_message'] = "Token Already used.";
 } else if(abs($curtime - $time) > 3600){
   $this->outputPHP['verify_message'] = "Token Expired.";
 } else {
   $stmt = $this->conn->prepare("UPDATE users SET emailv=1 WHERE email=:email");
   $stmt->execute(array(":email" => $row[0]['email']));
   $stmt = $this->conn->prepare("UPDATE emailverify SET verified=1 WHERE code=:code");
   $stmt->execute(array(":code" => $code));
   $this->outputPHP['verify_message'] = "OK";
   $this->signin('email');
 }
} else {
$this->outputPHP['verify_message'] = "Invalid Token";
}

?>
