<?php

require('function.php');

debug('☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆');
debug('☆　パスワード再発行認証キー入力ページ ');
debug('☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆');
debugLogStart();

if(empty($_SESSION['auth_key'])){
    header("Location:passRemindSend.php");
}

if(!empty($_POST)){
    debug('POST送信があります。');
    debug('POST情報：'.print_r($_POST,true));
    
    $auth_key = $_POST['token'];
    
    validRequired($auth_key,'token');
    
    if(empty($err_msg)){
        debug('未入力チェックOK.');
        
        validLength($auth_key,'token');
        
        validHalf($auth_key,'token');
        
        if(empty($err_msg)){
            debug('バリデーションOK。');
            
            if($auth_key !== $_SESSION['auth_key']){
                $err_msg['common'] = MGS15;
            }
            if(time() > $_SESSION['auth_key_limit']){
                $err_msg['common'] = MSG16;
            }
            
            if(empty($err_msg)){
                debug('認証OK。');
                
                $pass = makeRandKey();
                
                try{
                    $dbh = dbConnect();
                    $sql = 'UPDATE users SET password = :pass WHERE email = :email AND delete_flg = 0';
                    $data = array(':email'=>$_SESSION['auth_email'],':pass'=>password_hash($pass,PASSWORD_DEFAULT));//暗号化して登録
                    $stmt = queryPost($dbh,$sql,$data);
                    
                    if($stmt){
                        debug('クエリ成功。');
                        
                        $form = 'ruka7373@gmail.com';
                        $to = $_SESSION['auth_email'];
                        $subject = '【パスワード再発行完了】｜　ねこ図鑑';
                        $comment = <<<COT
                        本メールアドレス宛にパスワードの再発行を致しました。
                        下記のURLにて再発行パスワードをご入力頂き、ログインください。

                        ログインページ：https://ruka.sakura.ne.jp/nekozukan/login.php
                        再発行パスワード：{$pass}
                        ※ログイン後、パスワードのご変更をお願い致します

                        //////////////////////////////////
                        ねこ図鑑
                        URL https://ruka.sakura.ne.jp/nekozukan/
                        E-mail nekozukan@ruka.sakura.ne.jp
                        /////////////////////////////////
                        COT;
                        sendMail($form,$to,$subject,$comment);
                        
                        session_unset();
                        $_SESSION['msg_success'] = SUC03;
                        debug('セッション変数の中身:'.print_r($_SESSION,true));
                        
                        header("Location:login.php");
                        
                    }else{
                        debug('クエリに失敗しました。');
                        $err_msg['common'] = MSG07;
                    }
                }catch(Exception $e){
                    error_log('エラー発生:'.$e->getMessage());
                    $err_msg['common'] = MSG07;
                }
            }
            
        }
    }
}

?>



<?php
$siteTitle= 'ねこ図鑑 - パスワード再発行認証キー入力';
require('head.php');
?>
<body>
<div class="title">
<p id="js-show-msg" style="display:none;" class="msg-slide">
    <?php echo getSessionFlash('msg_success'); ?>
</p>
        <h1>認証キー入力</h1>
         </div>
         <section>
        <div class="form-wrap" style="height:600px;">
          <form action="" method="post" class="form" style="margin:0 auto;margin-top:30px;margin-bottom:30px;">
              <p>ご指定のメールアドレスにお送りした　パスワード再発行認証　メール内にある　「認証キー」をご入力ください。</p>
              <div class="area-msg">
                  <?php 
                  if(!empty($err_msg['common'])) echo $err_msg['common'];
                  ?>
              </div>
              <label class="<?php if(!empty($err_msg['token'])) echo 'err'; ?>">
             認証キー
              <input type="text" name="token" value="<?php echo getFormData('token'); ?>">
              </label>
              <div class="area-msg">
                  <?php 
                  if(!empty($err_msg['token'])) echo $err_msg['token'];
                  ?>
              </div>
              <div>
                 <input type="submit" class="btn" value="再発行する">
              </div>
           </form>
             <div style="position:relative;top:50px;">
           <a href="passRemindSend.php">&lt; パスワード再発行メールを再度送信する</a>
           </div>
    </div>
   </section>
</body>

<?php
         require('footer.php');
        ?>
