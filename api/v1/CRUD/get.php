<?php
  $userID1 = $this->userID;
  switch ($this->data[0]) {
    case 'feed':
      if(isset($this->request['from'])){
        $from = $this->request['from'];
      } else {
        $from = 0;
      }
      $select = "SELECT photos.*, users.email
      FROM photos
      INNER JOIN users ON users.id = photos.userid
      WHERE deleted = 0
      ORDER BY id DESC
      LIMIT ".$from.",10";
      $stmt = $this->conn->prepare($select);
      $stmt->execute();
      $this->outputPHP['results'] = $stmt->fetchAll();
      if(sizeof($this->outputPHP['results']) < 10){
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
        if(isset($this->request['from'])){
          $from = $this->request['from'];
        } else {
          $from = 0;
        }
        $select = "SELECT photos.*, users.email
        FROM photos
        INNER JOIN users ON users.id = photos.userid
        WHERE photos.userid = :userid AND photos.deleted = 0
        ORDER BY id DESC
        LIMIT ".$from.",10";
        $stmt = $this->conn->prepare($select);
        $stmt->execute(array("userid"=>$userID1));
        $this->outputPHP['results'] = $stmt->fetchAll();
      } else {
        $this->gotError(401);
      }
      break;
    case 'photo':
      if(isset($this->request['photoid'])){
      } else if (isset($this->data[1])){
        $this->request['photoid'] = $this->data[1];
      } else {
        $this->gotError(206);
        return false;
      }
      $select = 'SELECT photos.*, users.email
      FROM
        photos
      INNER JOIN users ON users.id = photos.userid
      WHERE
        photos.id = :photoid AND photos.deleted = 0
      LIMIT 1';
      $stmt = $this->conn->prepare($select);
      $stmt->execute(array('photoid' => $this->request['photoid']));
      $this->outputPHP['results'] = $stmt->fetchAll();

      $select = 'SELECT comments.*, users.email
      FROM
        comments
        INNER JOIN users ON users.id = comments.userid
      WHERE
        comments.photoid = :photoid AND comments.deleted = 0';
      $stmt = $this->conn->prepare($select);
      $stmt->execute(array('photoid' => $this->request['photoid']));
      $this->outputPHP['results'][0]['comments'] = $stmt->fetchAll();
      break;
  }
  if (isset($stmt)) {
      $this->statement = $stmt;
  }

  return false;
