<?php

require('function.php');

debug('☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆');
debug('☆　ユーザー登録ページ ');
debug('☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆');
debugLogStart();

//ログイン画面処理

if(!empty($_POST)){
    debug('POST送信があります。');
    
    //変数にユーザー情報を代入
$email = $_POST['email'];
$pass = $_POST['pass'];
$pass_re = $_POST['pass_re'];

    // debug('$email '.$email);
    // debug('$pass '.$pass);
    // debug('$pass_re '.$pass_re);
    debug('date '.date('Y-m-d H:i:s'));
    
    //未入力チェック
    validRequired($email,'email');
    validRequired($pass,'pass');
    validRequired($pass_re,'pass_re');
    
    //エラーメッセージが無ければバリデーションチェック
    if(empty($err_msg)){

        //Eメール
        validEmail($email,'email');
        validMaxLen($email,'email');
        validEmaildup($email);    
        
        //パスワード
        validHalf($pass,'pass');
        validMaxLen($pass,'pass');
        validMinLen($pass,'pass');
        
        //パスワード（再入力）
        validMaxLen($pass_re,'pass_re');
        validMinLen($pass_re,'pass_re');
        
        //エラーメッセージが無ければバリデーション続行
        if(empty($err_msg)){
            validMatch($pass,$pass_re,'pass_re');
            
            if(empty($err_msg)){
                try{
                    $dbh = dbConnect();
                    $sql = 'INSERT INTO users (email,login_time,password,create_date) VALUES(:email,:login_time,:password,:create_date)';
                    $data = array(':email'=>$email,':login_time'=>date('Y-m-d H:i:s'),':password'=>password_hash($pass,PASSWORD_DEFAULT),
                                 ':create_date'=>date('Y-m-d H:i:s'));
                    $stmt = queryPost($dbh,$sql,$data);
                    
                    //クエリ成功したら
                    if($stmt){
                        $sesLimit = 60*60;
                        $_SESSION['login_date'] = time();//現在日時
                        $_SESSION['login_limit'] = $sesLimit;//ログイン期限1時間
                        $_SESSION['user_id'] = $dbh->lastInsertId();//最後にインサートされたDBのIDを取得（登録直後なので）
                        
                        debug('セッション変数の中身:'.print_r($_SESSION,true));
                        
                        header("Location:mypage.php");
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
$siteTitle= 'ねこ図鑑 - ユーザー登録';
require('head.php');
?>
<body>
    <div class="title">
        <h1>ユーザー登録</h1>
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
                     パスワード<span style="font-size:12px">※英数字６文字以上</span>
                     <div class="area-msg">
                      <?php if(!empty($err_msg['pass'])) echo $err_msg['pass']; ?>
                  </div>
                 <input type="password" name="pass" value="<?php if(!empty($_POST['pass'])) echo $_POST['pass']; ?>">
                  </label>
                  
                  <label class="<?php if(!empty($err_msg['pass_re'])) echo 'err'; ?>">
                     パスワード（再入力）
                     <div class="area-msg">
                      <?php if(!empty($err_msg['pass_re'])) echo $err_msg['pass_re']; ?>
                  </div>
                 <input type="password" name="pass_re" value="<?php if(!empty($_POST['pass_re'])) echo $_POST['pass_re']; ?>">
                  </label>
                  <input type="submit" value="登録" class="btn">
             </form>
         </div>
          
    </div>
   
</body>

<?php
         require('footer.php');
        ?>
