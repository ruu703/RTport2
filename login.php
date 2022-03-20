<?php

require('function.php');

debug('☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆');
debug('☆　ログインページ ');
debug('☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆');
debugLogStart();

//ログイン認証
require('auth.php');

//ログイン画面処理
//POST送信があれば処理に入る
if(!empty($_POST)){
   // debug('POST送信があります。:'.print_r($_POST,true));
    
    //変数にユーザー情報を代入
$email = $_POST['email'];
$pass = $_POST['pass'];
$pass_save = (!empty($_POST['pass_save'])) ? true : false;
    
   validRequired($email,'email');
   validRequired($pass,'pass');

    if(empty($err_msg)){
        
         //emailチェック
        validEmail($email,'email');
        validMaxLen($email,'email');
        
        //パスワードチェック
        validHalf($pass,'pass');
        validMaxLen($pass,'pass');
        validMinLen($pass,'pass');
        
        if(empty($err_msg)){
            debug('バリデーションOKです。');
            
            try{//DB接続して,入力メールアドレスに該当するレコードを取得する
                $dbh = dbConnect();
                $sql = 'SELECT password,id FROM users WHERE email = :email AND delete_flg = 0';
                $data = array(':email'=>$email);
                $stmt = queryPost($dbh,$sql,$data);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                
                debug('クエリ結果の中身:'.print_r($result,true));
                
                //パスワード照合
                if(!empty($result) && password_verify($pass,array_shift($result))){
                    debug('パスワードがマッチしました。');
                    //debug('$resultの中身:'.print_r($result,true));
                        
                    $sesLimit = 60*60;
                    $_SESSION['login_date'] = time();
                    
                    if($pass_save){
                        debug('ログイン保持にチェックがあります。');
                        
                        $_SESSION['login_limit'] = $sesLimit *24 *30; //３０日間保持
                    }else{
                        debug('ログイン保持にチェックはありません。');
                        
                        $_SESSION['login_limit'] = $sesLimit;
                    }
                    
                    $_SESSION['user_id'] = $result['id'];
                    
                    debug('セッション変数の中身:'.print_r($_SESSION,true));
                    debug('マイページへ遷移します。');
                    header("Location:mypage.php");
                }else{
                    debug('パスワードがアンマッチです。');
                    $err_msg['common'] = MSG09;
                }
            }catch(Exception $e){
                error_log('エラー発生:'.$e->getMassage());
                $err_msg['common'] = MSG07;
            }
        }

    }
        
       
    }

debug('画面表示処理終了　☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆');
?>

<?php
$siteTitle= 'ねこ図鑑 - ログイン';
require('head.php');
?>
<body>
    <div class="title">
        <h1>ログイン</h1>
         </div>
         <div class="form-wrap">
         <div class="form2">
             <form action="" method="post" enctype="multipart/form-data">
                  <div class="area-msg">
                  <?php if(!empty($err_msg['common'])) echo $err_msg['common']; ?>
                  </div>
                  <label class="<?php if(!empty($err_msg['email'])) echo 'err'; ?>">
                     メールアドレス
                     <div class="area-msg">
                      <?php if(!empty($err_msg['email'])) echo $err_msg['email']; ?>
                  </div>
                 <input type="text" name="email" value="<?php if(!empty($_POST['email'])) echo $_POST['email']; ?>">
                  </label>
                  
                  <label class="<?php if(!empty($err_msg['pass'])) echo 'err'; ?>">
                     パスワード
                     <div class="area-msg">
                      <?php if(!empty($err_msg['pass'])) echo $err_msg['pass']; ?>
                  </div>
                 <input type="password" name="pass" value="<?php if(!empty($_POST['pass'])) echo $_POST['pass']; ?>">
                  </label>
                  
                  <label>
                      <input type="checkbox" name="pass_save">次回ログインを省略する
                  </label>
                 
                  <div style="overflow:hidden;">
                  <input type="submit" value="ログイン" class="btn">
                   </div>
             </form>
             パスワードを忘れた方は　<a href="passRemindSend.php">こちら</a>
         </div>
    </div>
   
</body>

<?php
         require('footer.php');
        ?>
