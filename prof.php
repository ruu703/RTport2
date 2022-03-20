<?php

require('function.php');

debug('☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆');
debug('☆　プロフィールページ ');
debug('☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆');
debugLogStart();

require('auth.php');

//画面処理
//DBからユーザー情報を取得
$dbFormData = getUser($_SESSION['user_id']);

debug('取得したユーザー情報:'.print_r($dbFormData,true));

if(!empty($_POST)){
    debug('POST送信があります。');
    debug('POST情報:'.print_r($_POST,true));
    debug('FILE情報:'.print_r($_FILES,true));
    
    //変数にユーザー情報を代入
    $username = $_POST['username'];
    $gender = $_POST['gender'];
    $birthday = $_POST['birthday'];
    $email = $_POST['email'];
    $gender = $_POST['gender'];
    //$pass = (!empty($_POST['pass']))? $_POST['pass'] : $;
    //$pass_re = (!empty($_POST['pass_re']))? $_POST['pass_re'] : '';
    $pic = (!empty($_FILES['pic']['name'])) ? uploadImg($_FILES['pic'],'pic') : '';
    $pic = (empty($pic)&&!empty($dbFormData['pic']))? $dbFormData['pic'] : $pic;
    
    if($dbFormData['username']!== $username){
        validmaxLen($username,'username');
    }
   if($dbFormData['email']!==$email){
       //最大文字数チェック
       validMaxLen($email,'email');
       //重複チェック
       if(empty($err_msg['email'])){
           validEmailDup($email);
       }
    　　validEmail($email,'email');
       validRequired($email,'email');
   }
    //if(!empty($dbFormData['password']!== $pass)){
        //validHalf($pass,'pass');
        //validMaxLen($pass,'pass');
        //validMinLen($pass,'pass');
        
        //validMaxLen($pass_re,'pass_re');
        //validMinLen($pass_re,'pass_re');
        
        //if(empty($err_msg)){
        //    validMatch($pass,$pass_re,'pass_re');
       // }
    //}
if(empty($err_msg)){
    debug('バリデーションOKです。');
    try{
        $dbh = dbConnect();
        $sql = 'UPDATE users SET username = :u_name,gender=:gender,birth=:b_day,email=:email,pic=:pic WHERE id=:u_id';
        $data = array(':u_name'=>$username,':gender'=>$gender,':b_day'=>$birthday,':email'=>$email,':pic'=>$pic,':u_id'=>$dbFormData['id']);
        $stmt = queryPost($dbh,$sql,$data);
        
        if($stmt){
            $_SESSION['msg_success']=SUC02;
            debug('マイページへ遷移します。');
            header("Location:mypage.php");
        }
    }catch(Exception $e){
        error_log('エラー発生:'.$e->getMessage());
        $err_msg['common'] = MSG07;
    }
}
}
debug('画面表示処理終了　☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆');
?>
<?php
$siteTitle= 'ねこ図鑑 - プロフィール';
require('head.php');
?>
<body>
    <div class="title">
        <h1>プロフィール</h1>
         </div>
         <div class="form-wrap">
         <div class="form">
             <form action="" method="post" enctype="multipart/form-data">
                 <div class="area-msg">
                     <?php
                     if(!empty($err_msg['common'])) echo $err_msg['common']; 
                     ?>
                 </div>
                  <label class="<?php if(!empty($err_msg['username'])) echo 'err'; ?>">
                      名前
                       <div class="area-msg"><?php
                     if(!empty($err_msg['username'])) echo $err_msg['username']; 
                     ?></div>
                 <input type="text" name="username" value="<?php echo getFormData('username'); ?>">
                  </label>
                   
                  <label class="<?php if(!empty($err_msg['gender'])) echo 'err'; ?>">
                      性別<br>
                       <div class="area-msg"><?php
                     if(!empty($err_msg['gender'])) echo $err_msg['gender']; 
                     ?></div>
                  <input type="radio" name="gender" value="女性"<?php echo (getFormData('gender') === "女性")? "checked":""; ?>>女性
                  <input type="radio" name="gender" value="男性"<?php echo (getFormData('gender') === "男性")? "checked":""; ?>>男性
                  </label>
                  
                  <label class="<?php if(!empty($err_msg['birthday'])) echo 'err'; ?>">
                      生年月日
                       <div class="area-msg"><?php
                     if(!empty($err_msg['birthday'])) echo $err_msg['birthday']; 
                     ?></div>
                 <input type="date" name="birthday" value="<?php echo getFormData('birth'); ?>">
                  </label>
                  
                  <label class="<?php if(!empty($err_msg['email'])) echo 'err'; ?>">
                     メールアドレス
                      <div class="area-msg">
                      <?php
                     if(!empty($err_msg['email'])) echo $err_msg['email']; 
                     ?>
                     </div>
                 <input type="text" name="email" value="<?php echo getFormData('email'); ?>">
                  </label>
                  <!--
                  <label class="<?php if(!empty($err_msg['pass'])) echo 'err'; ?>">
                     パスワード
                      <div class="area-msg"><?php
                     if(!empty($err_msg['pass'])) echo $err_msg['pass']; 
                     ?></div>
                 <input type="password" name="pass" value="<?php if(!empty($pass)) echo $pass; ?>">
                  </label>
                  
                  <label class="<?php if(!empty($err_msg['pass_re'])) echo 'err'; ?>" >
                     パスワード（再入力）
                     <div class="area-msg"> <?php
                     if(!empty($err_msg['pass_re'])) echo $err_msg['pass_re']; 
                     ?></div>
                 <input type="password" name="pass_re" value="<?php if(!empty($pass_re)) echo $pass_re; ?>">
                  </label>
                  -->
                     プロフィール画像
                      <div class="area-msg">
                         <?php if(!empty($err_msg['pic'])) echo $err_msg['pic']; 
                         ?>
                     </div>
                     <label class="area-drop <?php if(!empty($err_msg['pic'])) echo 'err'; ?>" style="height:450px;line-height:450px;">
                 <input type="hidden" name="MAX_FILE_SIZE" value="3145728">
                 <input type="file" name="pic" class="profpic" style="height:450px;">
                 <img src="<?php echo getFormData('pic'); ?>" alt="" class="prev-img" style="<?php if(empty(getFormData('pic'))) echo 'display:none;' ?>">
                 ドラッグ ＆　ドロップ
                  </label>
                  <div class="btn-container">
                  <input type="submit" value="登録" class="btn">
                  </div>
             </form>
         </div>
         
         <?php require('side.php'); ?>
         
    </div>
   
</body>

<?php
         require('footer.php');
        ?>
