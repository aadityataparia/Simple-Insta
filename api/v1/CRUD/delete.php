<?php

$userID1 = $this->userID;
switch ($this->data[0]) {
  case 'photo':
    $update = "
    UPDATE `photos`
    SET deleted=1
    WHERE id=:photoid AND userid = :userid";
    $stmt=$this->conn->prepare($update);
    $stmt->execute(array("photoid"=>$this->request['photoid'],"userid"=>$userID1));
    $id = $this->conn->lastInsertId();
    $this->outputPHP['deleted_id'] = $id;
    $path = "./uploads/";
    $dst = $path . 'photos/' . $id . '.jpg';
    unlink($dst);
    break;
  case 'comment':
    $update = "
    UPDATE `comments`
    SET deleted=1
    WHERE id=:commentid AND userid = :userid";
    $stmt=$this->conn->prepare($update);
    $stmt->execute(array("commentid"=>$this->request['commentid'],"userid"=>$userID1));
    $id = $this->conn->lastInsertId();
    $this->outputPHP['deleted_id'] = $id;
    break;
}
if (isset($stmt)) {
    $this->statement = $stmt;
}

return false;
