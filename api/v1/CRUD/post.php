<?php
use Razorpay\Api\Api;

$userID1 = $this->userID;
if(!isset($userID1) || !$userID1){
  $this->gotError(401);
  return false;
}
$a = array();
$size = 0;
switch ($this->data[0]) {
  case 'dp':
    $path = "./uploads/";
    $img = $_FILES['photoimg']['tmp_name'];
    $dst = $path . 'dps/' . $userID1;
    if (($img_info = getimagesize($img)) === FALSE){
      $this->gotError(1000);
      $this->outputPHP['customerror'] = "Invalid image";
      return false;
    }

    switch ($img_info[2]) {
      case IMAGETYPE_GIF  : $src = imagecreatefromgif($img);  break;
      case IMAGETYPE_JPEG : $src = imagecreatefromjpeg($img); break;
      case IMAGETYPE_PNG  : $src = imagecreatefrompng($img);  break;
      default :
        $this->gotError(1000);
        $this->outputPHP['customerror'] = "Unknown image type";
        return false;
    }

    // size from
    $height = 250;
    $width = 250;
    list($w, $h) = getimagesize($img);
    $tmp = imagecreatetruecolor($w,$h);

    if($w > $h) {
            $new_height =   $height;
            $new_width  =   floor($w * ($new_height / $h));
            $crop_x     =   ceil(($w - $h) / 2);
            $crop_y     =   0;
        }
    else {
            $new_width  =   $width;
            $new_height =   floor( $h * ( $new_width / $w ));
            $crop_x     =   0;
            $crop_y     =   ceil(($h - $w) / 2);
        }

    // I think this is where you are mainly going wrong
    $tmp = imagecreatetruecolor($width,$height);

    imagecopyresampled($tmp, $src, 0, 0, $crop_x, $crop_y, $new_width, $new_height, $w, $h);
    imagejpeg($tmp, $dst);
    $this->data[0] = 'user';
    $this->GETq();
    break;
  case 'photo':
    $insert="INSERT into photos (userid,deleted) VALUES(?,0)";
    $stmt=$this->conn->prepare($insert);
    $stmt->execute(array($userID1));
    $id = $this->conn->lastInsertId();
    $this->outputPHP['inserted_id'] = $id;

    $path = "./uploads/";
    $img = $_FILES['photoimg']['tmp_name'];
    $dst = $path . 'photos/' . $id;
    if (($img_info = getimagesize($img)) === FALSE){
      $this->gotError(1000);
      $this->outputPHP['customerror'] = "Invalid image";
      return false;
    }

    switch ($img_info[2]) {
      case IMAGETYPE_GIF  : $src = imagecreatefromgif($img);  break;
      case IMAGETYPE_JPEG : $src = imagecreatefromjpeg($img); break;
      case IMAGETYPE_PNG  : $src = imagecreatefrompng($img);  break;
      default :
        $this->gotError(1000);
        $this->outputPHP['customerror'] = "Unknown image type";
        return false;
    }

    // size from
    $height = 1000;
    $width = 1000;
    list($w, $h) = getimagesize($img);
    $tmp = imagecreatetruecolor($w,$h);

    if($w > $h) {
            $new_height =   $height;
            $new_width  =   floor($w * ($new_height / $h));
            $crop_x     =   ceil(($w - $h) / 2);
            $crop_y     =   0;
        }
    else {
            $new_width  =   $width;
            $new_height =   floor( $h * ( $new_width / $w ));
            $crop_x     =   0;
            $crop_y     =   ceil(($h - $w) / 2);
        }

    // I think this is where you are mainly going wrong
    $tmp = imagecreatetruecolor($width,$height);

    imagecopyresampled($tmp, $src, 0, 0, $crop_x, $crop_y, $new_width, $new_height, $w, $h);
    imagejpeg($tmp, $dst);

    $this->data[0] = 'photo';
    $this->data[1] = $id;
    $this->GETq();
    break;
  case 'comment':
    $insert="INSERT into comments (photoid,comment,userid) VALUES(?,?,?)";
    $stmt=$this->conn->prepare($insert);
    $stmt->execute(array($this->request['photoid'],$this->request['comment'],$userID1));
    $id = $this->conn->lastInsertId();
    $this->outputPHP['inserted_id'] = $id;
    $this->data[0] = 'photo';
    $this->data[1] = $this->request['photoid'];
    $this->GETq();
    break;
}

if (isset($stmt)) {
    $this->statement = $stmt;
}
return false;
?>
