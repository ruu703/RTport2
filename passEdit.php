<?php

require('function.php');

debug('☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆');
debug('☆　パスワード変更ページ ');
debug('☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆');
debugLogStart();

require('auth.php');

$userData = getUser($_SESSION['user_id']);
debug('取得したユーザー情報：'.print_r($userData,true));

if(!empty($_POST)){
    debug('POST送信があります。');
    debug('POST情報：'.print_r($_POST,true));
    
    $pass_old = $_POST['pass_old'];
    $pass_new = $_POST['pass_new'];
    $pass_new_re = $_POST['pass_new_re'];
    
    validRequired($pass_old,'pass_old');//未入力チェック
    validRequired($pass_new,'pass_new');
    validRequired($pass_new_re,'pass_new_re');
    
    if(empty($err_msg)){
        debug('未入力チェックOK。');
        
        //パスワードチェック
        validPass($pass_old,'pass_old');//半角、最小、最大チェック
        validPass($pass_new,'pass_new');
        
        if(!password_verify($pass_old,$userData['password'])){//古いパスワードとDB登録のものが合っているか　ハッシュ化されたものと比較
            $err_msg['pass_old'] = MSG12;
        }
        if($pass_old === $pass_new){//古いパスワードと、新しいパスワードが同じでないか
            $err_msg['pass_new'] = MSG13;
        }
        
        validMatch($pass_new,$pass_new_re,'pass_new_re');//新しいパスワードと、新しいパスワード（再入力）が同じか
        
        if(empty($err_msg)){
            debug('バリデーションOK。');
            
            try{
                $dbh = dbConnect();
                $sql = 'UPDATE users SET password = :pass WHERE id = :id';
                $data = array(':id' => $_SESSION['user_id'], ':pass' => password_hash($pass_new,PASSWORD_DEFAULT));//ハッシュ化する
                $stmt = queryPost($dbh,$sql,$data);
                
                if($stmt){
                    $_SESSION['msg_success'] = SUC01;
                    
                    $username = ($userData['username']) ? $userData['username'] : '名無し';//ユーザー名　
                    $from = '送信元メールアドレス';//送信元メールアドレス
                    $to = $userData['email'];//送信先メールアドレス
                    $subject = 'パスワード変更通知　|  ねこ図鑑';//件名
                    $comment = <<<TEXT
{$username} さん
パスワードが変更されました。

//////////////////////////////////////
ねこ図鑑
URL https://ruka.sakura.ne.jp/nekozukan/
E-mail nekozukan@ruka.sakura.ne.jp
//////////////////////////////////////
TEXT;
            sendMail($from,$to,$subject,$comment);
                    
                    header("Location:mypage.php");
                }
            }catch (Exception $e){
                error_log('エラー発生:'.$e->getMessage());
                $err_msg['common'] = MSG07;
            }
            
        }
    }
}
?>

<?php
$siteTitle= 'ねこ図鑑 - マイページ';
require('head.php');
?>
<body>
<div class="title">

        <h1>パスワード変更</h1>
         </div>
         <section>
        <div class="form-wrap">
          <form action="" method="post" class="form";>
              <div class="area-msg">
                  <?php
                  echo getErrMsg('common');
                  ?>
              </div>
              <label class="<?php if(!empty($err_msg['pass_old'])) echo 'err'; ?>">
              古いパスワード
              <input type="password" name="pass_old" value="<?php echo getFormData('pass_old'); ?>">
              </label>
              <div class="area-msg">
                  <?php
                  echo getErrMsg('pass_old');
                  ?>
              </div>
              <label class="<?php if(!empty($err_msg['pass_new'])) echo 'err'; ?>">
              新しいパスワード
              <input type="password" name="pass_new" value="<?php echo getFormData('pass_new'); ?>">
              </label>
              <div class="area-msg">
                  <?php
                  echo getErrMsg('pass_new');
                  ?>
              </div>
              <label class="<?php if(!empty($err_msg['pass_new_re'])) echo 'err'; ?>">
              新しいパスワード（再入力）
              <input type="password" name="pass_new_re" value="<?php echo getFormData('pass_new_re'); ?>">
              </label>
              <div class="area-msg">
                  <?php
                  echo getErrMsg('pass_new');
                  ?>
              </div>
              <div class="btn-container">
                  <input type="submit" value="変更する" class="btn">
                  </div>
           </form>
          
          
          <?php require('side.php'); ?>
    </div>
   </section>
</body>

<?php
         require('footer.php');
        ?>
