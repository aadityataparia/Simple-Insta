<?php
if(empty($this->request['email']) || empty($this->request['code']))
{
  $this->gotError(206);
  return false;
}

if(isset($this->request['email']) && isset($this->request['code']))
{
 $email = $this->request['email'];
 $code = $this->request['code'];
 $statusY = 1;
 $statusN = 0;
 $stmt = $this->conn->prepare("SELECT id,code,email,verified,time_added FROM emailverify WHERE email=:email AND code=:code LIMIT 1");
 $stmt->execute(array(":email"=>$email,":code"=>$code));
 $row=$stmt->fetchAll();
 $time = strtotime($row[0]['time_added']);
 $curtime = time();
 if(sizeof($row) > 0) {
   if($row[0]['verified']){
     $this->outputPHP['verify_message'] = "Token Already used.";
   } else if(abs($curtime - $time) > 3600){
     $this->outputPHP['verify_message'] = "Token Expired.";
   } else {
     $stmt = $this->conn->prepare("UPDATE users SET emailv=:status WHERE email=:email");
     $stmt->execute(array('status' => $statusY, ":email" => $row[0]['email']));
     $stmt = $this->conn->prepare("UPDATE emailverify SET verified=:status WHERE code=:code");
     $stmt->execute(array('status' => $statusY, ":code" => $code));
     $this->outputPHP['verify_message'] = "OK";
     $this->signin('email');
   }
 } else {
  $this->outputPHP['verify_message'] = "Invalid Token";
 }
}

?>
