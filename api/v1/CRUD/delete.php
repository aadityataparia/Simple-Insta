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
    break;
}
if (isset($stmt)) {
    $this->statement = $stmt;
}

return false;
