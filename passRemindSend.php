<?php

require('function.php');

debug('☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆');
debug('☆　パスワード再発行メール送信ページ ');
debug('☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆');
debugLogStart();


if(!empty($_POST)){
debug('POST送信があります。');
debug('POST情報：'.print_r($_POST,true));
    
    $email = $_POST['email'];
    
    validRequired($email,'email');
    
    if(empty($err_msg)){
        debug('未入力チェックOK。');
        
        validEmail($email,'email');
        
        validMaxLen($email,'email');
        
        if(empty($err_msg)){
            debug('バリデーションOK。');
            
            try{
                $dbh = dbConnect();
                $sql = 'SELECT count(*) FROM users WHERE email = :email AND delete_flg = 0';
                $data = array(':email' => $email);
                $stmt = queryPost($dbh,$sql,$data);
                $result = $stmt->fetch();
                
                if($stmt && array_shift($result)){
                    debug('クエリ成功。DB登録あり。');
                    $_SESSION['msg_success'] = SUC03;
                    
                    $auth_key = makeRandKey();
                    
                    $form = 'ruka7373@gmail.com';
                    $to = $email;
                    $subject = ' パスワード再発行認証　|　ねこ図鑑';
                    $comment = <<<COT
                    本メールアドレス宛にパスワード再発行のご依頼がありました。
                    下記のURLにて認証キーをご入力頂くとパスワードが再発行されます。

                    パスワード灰発行認証キー入力ページ: http://localhost/WEBUKATU/op/nekozukan/passRemindRecieve.php
                    認証キー　: {$auth_key}
                    ※認証キーの有効期限は30分となります

                    認証キーの再発行は下記ページより再度、再発行をお願い致します。
                    http://localhost/WEBUKATU/op/nekozukan/passRemindSend.php


                    //////////////////////////////////
                    ねこ図鑑
                    URL http://nekozukan.com/
                    E-mail info@nekozukan.com
                    /////////////////////////////////
                    COT;
                    
                    sendMail($form,$to,$subject,$comment);
                    
                    $_SESSION['auth_key'] = $auth_key;
                    $_SESSION['auth_email'] = $email;
                    $_SESSION['auth_key_limit'] =time()+(60*30);//30分
                    debug('セッション変数の中身'.print_r($_SESSION,true));
                    
                    header("Location:passRemindRecieve.php");
                    
                }else{
                    debug('クエリに失敗したかDBに登録のないEmailが入力されました。');
                    $err_msg['common'] = MSG07;
                }
            }catch(Exception $e){
                error_log('エラー発生:'.$e->getMessage());
                $err_msg['common'] = MSG07;
            }
        }
    }
}
?>



<?php
$siteTitle= 'ねこ図鑑 - パスワード再発行メール送信';
require('head.php');
?>
<body>
<div class="title">

        <h1>パスワード再発行</h1>
         </div>
         <section>
        <div class="form-wrap" style="height:600px;">
          <form action="" method="post" class="form" style="margin:0 auto;margin-top:30px;margin-bottom:30px;">
              <p>ご指定のメールアドレス宛にパスワード再発行用のURLと認証キーをお送り致します。</p>
              <div class="area-msg">
                  <?php 
                  if(!empty($err_msg['common'])) echo $err_msg['common'];
                  ?>
              </div>
              <label class="<?php if(!empty($err_msg['email'])) echo 'err'; ?>">
              Email
              <input type="text" name="email" value="<?php echo getFormData('email'); ?>">
              </label>
              <div>
                 <input type="submit" class="btn" value="送信する">
              </div>
             
           </form>
            <div style="position:relative;top:50px;">
              <a href="mypage.php">&lt; マイページに戻る</a>
              </div>
    </div>
   </section>
</body>

<?php
         require('footer.php');
        ?>
