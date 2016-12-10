<?php
  $userID1 = $this->userID;
  switch ($this->data[0]) {
    case 'feed':
      $from = $this->request['from'] || 0;
        $select = "SELECT *
        FROM photos
        LIMIT ".$from.",10";
        $stmt = $this->conn->prepare($select);
        $stmt->execute();
        $this->outputPHP['results']['photos'] = $stmt->fetchAll();
        if(sizeof($this->outputPHP['results']['photos']) < 10){
          $this->outputPHP['more'] = false;
        } else {
          $this->outputPHP['more'] = true;
        }
      break;
    case 'user':
      if(isset($userID1) && is_numeric($userID1)) {
          $select = "SELECT *, CHAR_LENGTH(password) as password
          FROM users
          WHERE id = :id LIMIT 1";
          $stmt = $this->conn->prepare($select);
          $stmt->execute(array('id' => $userID1));
          $this->outputPHP['results'] = $stmt->fetchAll();
      } else {
        $this->gotError(401);
      }
      break;
    case 'myphotos':
      if(isset($userID1) && is_numeric($userID1)) {
        $select = "SELECT *
        FROM photos
        LIMIT ".$from.",10
        WHERE userid = :userid";
        $stmt = $this->conn->prepare($select);
        $stmt->execute(array("userid"=>$userID1));
        $this->outputPHP['results']['myphotos'] = $stmt->fetchAll();
      } else {
        $this->gotError(401);
      }
      break;
    case 'photo':
      if(!isset($this->request['photoid']) && !isset($this->data[1])){
        $this->gotError(206);
        return false;
      }
      $this->request['photoid'] = $this->data[1] || $this->request['photoid'];
      $select = 'SELECT
       *
      FROM
        photos
      WHERE
        id = :photoid
      LIMIT 1';
      $stmt = $this->conn->prepare($select);
      $stmt->execute(array('photoid' => $this->request['photoid']));
      $this->outputPHP['results']['photo'] = $stmt->fetch();

      $select = 'SELECT
       *
      FROM
        comments
      WHERE
        photoid = :photoid
      LIMIT 1';
      $stmt = $this->conn->prepare($select);
      $stmt->execute(array('photoid' => $this->request['photoid']));
      $this->outputPHP['results']['comments'] = $stmt->fetchAll();
      break;
  }
  if (isset($stmt)) {
      $this->statement = $stmt;
  }

  return false;
