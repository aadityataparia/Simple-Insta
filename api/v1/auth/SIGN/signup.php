<?php

function array_to_notnull_params($array) {
  $temp = array();
  foreach (array_keys($array) as $name) {
    $temp[] = "`$name` = IFNULL($name,?)";
  }
  return implode(', ', $temp);
}
function array_to_pdo_params($array) {
  $temp = array();
  foreach (array_keys($array) as $name) {
    $temp[] = "`$name` = ?";
  }
  return implode(', ', $temp);
}
function array_to_where_params($array) {
  $temp = array();
  foreach (array_keys($array) as $name) {
    $temp[] = "`$name` = ?";
  }
  return implode(' AND ', $temp);
}
function toHash($pass){
    return password_hash($pass,PASSWORD_DEFAULT);
}
function verifyhash($pass, $hash){
  return password_verify($pass, $hash);
}

if($this->request['type'] == 'password') {
  $required = ['email','password'];
} else {
  $this->gotError(206);
}

foreach($required as $val){
  if(isset($this->request[$val])){
    $a[$val] = $this->request[$val];
  } else {
    $this->gotError(206);
    return false;
  }
}

if($this->request['type'] == 'password') {
  $hash = toHash($this->request['password']);
  if($this->request['what'] == 'signin'){
    $select="SELECT id, password
    from users
    WHERE email = :email LIMIT 1";
    $stmt=$this->conn->prepare($select);
    $stmt->execute(array("email" => $this->request['email']));
    $this->outputPHP['type'] = 'user';
    $res = $stmt->fetchAll();
    $this->outputPHP["function"] = "signin";
    if(sizeof($res) < 1){
      $this->gotError(404);
      return false;
    } else if(strlen($res[0]['password']) < 10){
      $this->outputPHP['ok'] = false;
      $this->outputPHP['status'] = 400;
      $this->outputPHP['message'] = "You have not set password yet. Signup with Magic Token and set your password.";
      return false;
    } else if(!verifyhash($this->request['password'],$res[0]['password'])){
      $this->gotError(401);
      return false;
    }
  } else {
    $this->gotError(206);
    return false;
  }
}

$this->signin('email');

if (isset($stmt)) {
    $this->statement = $stmt;
}
return false;
