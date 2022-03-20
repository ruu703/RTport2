<?php

require('function.php');

debug('☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆');
debug('☆　退会ページ ');
debug('☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆');
debugLogStart();

require('auth.php');

if(!empty($_POST)){
    debug('POST送信があります。');
    
    try{
        $dbh = dbConnect();
        $sql1 = 'UPDATE users SET delete_flg = 1 WHERE id = :u_id';
        $sql2 = 'UPDATE cats SET delete_flg = 1 WHERE user_id = :u_id';
        $data = array('u_id' => $_SESSION['user_id']);
        $stmt1 = queryPost($dbh,$sql1,$data);
        $stmt2 = queryPost($dbh,$sql2,$data);
        
        if($stmt1 && $stmt2){
            session_destroy();
            debug('セッション変数の中身:'.print_r($_SESSION,true));
            debug('トップページへ遷移します。');
            header("Location:index.php");
        }else{
            debug('クエリが失敗しました。');
            $err_msg['common'] = MSG07;
        }
    }catch(Exception $e){
        error_log('エラー発生：'.$e->getMessage());
        $err_msg['common'] = MSG07;
    }
}
debug('画面表示処理終了　☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆');
?>


<?php
$siteTitle= 'ねこ図鑑 - 退会';
require('head.php');
?>
<body>
<div class="title">
<p id="js-show-msg" style="display:none;" class="msg-slide">
    <?php echo getSessionFlash('msg_success'); ?>
</p>
        <h1>退会</h1>
         </div>
         <section>
        <div class="form-wrap" style="height:600px;">
          <form action="" method="post" class="form" style="margin:0 auto;margin-top:30px;margin-bottom:30px;">
              <p style="margin:50px;">ご登録いただいた猫ちゃん達も削除されます。<br>
              本当に退会しますか？</p>
              <div class="area-msg">
                  <?php 
                  if(!empty($err_msg['common'])) echo $err_msg['common'];
                  ?>
              </div>
              <div style="float: none;overflow:hidden;magin:30px;text-align:center;height:100px;">
           <a href="mypage.php" >&lt; マイページに戻る</a>
           </div>
              <div style="text-align:center;">
                 <input type="submit" class="btn" name="submit" value="退会する" style="float: none;overflow:hidden;"><!-- $_POST[submit]='退会する'-->
              </div>
           </form>
    </div>
   </section>
</body>

<?php
         require('footer.php');
        ?>
