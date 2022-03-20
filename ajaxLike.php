<?php

require('function.php');

debug('☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆');
debug('☆　Ajax ');
debug('☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆');
debugLogStart();

//=============================
// Ajax処理
//=============================

if(isset($_POST['catId'])&& isset($_SESSION['user_id']) && isLogin()) {
    debug('POST送信があります。');
    $n_id = $_POST['catId'];
    debug('ねこちゃんID:'.$n_id);
    
    try{
        $dbh = dbConnect();
        $sql = 'SELECT * FROM `like` WHERE cat_id = :n_id AND user_id = :u_id';
        $data = array(':u_id'=>$_SESSION['user_id'],':n_id'=>$n_id);
        $stmt = queryPost($dbh,$sql,$data);
        $resultCount = $stmt->rowCount();
        debug($resultCount);
        
        if(!empty($resultCount)){
            $sql = 'DELETE FROM `like` WHERE cat_id = :n_id AND user_id = :u_id';
            $data = array(':u_id'=>$_SESSION['user_id'],':n_id'=>$n_id);
            $stmt = queryPost($dbh,$sql,$data);
    }else{
            $sql = 'INSERT INTO `like` (cat_id,user_id,create_date) VALUES (:n_id,:u_id,:date)';
            $data = array(':u_id'=>$_SESSION['user_id'],':n_id'=>$n_id,':date'=>date('Y-m-d H:i:s'));
            $stmt = queryPost($dbh,$sql,$data);
        }
}catch(Exception $e){
        error_log('エラー発生:'.$e->getMessage());
    }
}
debug('Ajax処理終了　☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆');
?>
