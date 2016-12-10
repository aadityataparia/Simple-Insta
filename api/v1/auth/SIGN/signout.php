<?php
setcookie('userid', 'sd', time()-60*60*24*30);
setcookie('seesionid', session_id(), time()-60*60*24*30);
$this->outputPHP['logged'] = false;
