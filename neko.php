<?php

require('function.php');

debug('☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆');
debug('☆　ねこちゃん登録ページ ');
debug('☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆');
debugLogStart();

require('auth.php');

$n_id = (!empty($_GET['n_id']))? $_GET['n_id'] : '';

$dbFormData = (!empty($n_id))? getNeko($_SESSION['user_id'],$n_id) : '';

$edit_flg = (empty($dbFormData)) ? false : true;

$dbCategoryData = getCategory();

//debug('$_GETの中身:'.print_r($_GET,true));
debug('ねこちゃんID:'.$n_id);
debug('フォーム表示用DBデータ:'.print_r($dbFormData,true));
//debug('カテゴリデータ:'.print_r($dbCategoryData,true));

//パラメータ改ざんチェック
if(!empty($n_id) && empty($dbFormData)){
    debug('GETパラメータのねこちゃんIDが違います。マイページへ遷移します。');
    header("Location:mypage.php");
}

//POST送信時処理
if(!empty($_POST)){
    debug('POST送信があります。');
    debug('POST情報:'.print_r($_POST,true));
    debug('FILE情報:'.print_r($_FILES,true));
      
$c_name = $_POST['catname'];
$c_gender = (!empty($_POST['gender']))? $_POST['gender'] : '';
$c_birth = $_POST['birth'];
$type = $_POST['type_id'];
$detail = $_POST['des'];

$pic1 = (!empty($_FILES['pic1']['name'])) ? uploadImg($_FILES['pic1'],'pic1') : '';//POSTされていたら、ファイルのアドレスが入る
$pic1 = (empty($pic1)&&!empty($dbFormData['pic1'])) ? $dbFormData['pic1'] : $pic1;//POSTされておらず、DBに登録されていたらDBのファイルが入る
$pic2 = (!empty($_FILES['pic2']['name'])) ? uploadImg($_FILES['pic2'],'pic2') : '';
$pic2 = (empty($pic2)&&!empty($dbFormData['pic2'])) ? $dbFormData['pic2'] : $pic2;
 $pic3 = (!empty($_FILES['pic3']['name'])) ? uploadImg($_FILES['pic3'],'pic3') : '';
$pic3 = (empty($pic3)&&!empty($dbFormData['pic3'])) ? $dbFormData['pic3'] : $pic3;  
$pic4 = (!empty($_FILES['pic4']['name'])) ? uploadImg($_FILES['pic4'],'pic4') : '';
$pic4 = (empty($pic4)&&!empty($dbFormData['pic4'])) ? $dbFormData['pic4'] : $pic4;
    
    if(empty($dbFromData)){//DB情報無し=新規登録ならバリデーションチェック
        
        validRequired($c_name,'catname');
        validMaxLen($c_name,'catname');
        
        validSelect($type,'type_id');
        validMaxLen($detail,'des',500);
        
        validRequired($pic1,'pic1');
        
    }else{
        if($dbFormData['catname'] !== $c_name){//DB登録あり、POSTと違う場合
            validRequired($c_name,'catname');
            validMaxLen($c_name,'catname');
        }
        if($dbFormData['type_id'] !== $type){
            validSelect($type,'type_id');
        }
        if($dbFormData['des'] !== $detail){
            validMaxLen($detail,'des',500);
        }
    }
    
    if(empty($err_msg)){
        debug('バリデーションOKです。');
        
        try{
            $dbh = dbConnect();
            if($edit_flg){
                debug('DB更新です。');
                $sql = 'UPDATE cats SET catname = :name,gender = :gender,birth = :birth,type_id = :type,pic1 = :pic1,pic2 = :pic2,pic3 = :pic3,pic4 = :pic4,des = :detail WHERE user_id = :u_id AND id = :n_id';
                $data = array(':name'=>$c_name,':gender'=>$c_gender,':birth'=>$c_birth,':type'=>$type,':pic1'=>$pic1,':pic2'=>$pic2,'pic3'=>$pic3,'pic4'=>$pic4,':detail'=>$detail,'u_id'=>$_SESSION['user_id'],':n_id'=>$n_id);
            }else{
                debug('DB新規登録です。');
                $sql = 'insert into cats (catname,gender,birth,type_id,pic1,pic2,pic3,pic4,des,user_id,create_date) values (:name,:gender,:birth,:type,:pic1,:pic2,:pic3,:pic4,:detail,:u_id,:date)';
                $data = array(':name'=>$c_name,':gender'=>$c_gender,':birth'=>$c_birth,':type'=>$type,':pic1'=>$pic1,':pic2'=>$pic2,':pic3'=>$pic3,':pic4'=>$pic4,':detail'=>$detail,':u_id'=>$_SESSION['user_id'],':date'=>date('Y-m-d H:i:s'));
            }
            debug('SQL:'.$sql);
            debug('流し込みデータ:'.print_r($data,true));
            $stmt = queryPost($dbh,$sql,$data);
            
            if($stmt){

                // 掲示板データ作成
                // if(!empty($_POST['submit'])){
                    debug('新規登録。掲示板を作成します。');
                    try{
                        $dbh = dbConnect();
                        $sql = 'SELECT id FROM cats ORDER BY id ASC';
                        $data = array();
                        $stmt = queryPost($dbh,$sql,$data);
                        $rst = $stmt->fetchAll(PDO::FETCH_COLUMN);
                        
                        if($rst){
                           debug('$rst中身:'.print_r($rst,true));
                        $catid = array_pop($rst);
                        // $catid = $rst->lastInsertId();
                        debug('$catid中身:'.print_r($catid,true));
                            
                        $sql = 'INSERT INTO bord (owner,cat_id,create_date) VALUES(:owner,:cat_id,:date)';
                        $data = array(':owner'=>$_SESSION['user_id'],':cat_id'=>$catid,':date'=>date('Y-m-d H:i:s'));
                        $stmt = queryPost($dbh,$sql,$data);
                           
                        }
                    }catch(Exception $e){
                        error_log('エラー発生:'.$e->getMessage());
                        $err_msg['common'] = MSG07;
                    }
                // }

                $_SESSION['msg_success'] = SUC04;
                debug('マイページへ遷移します。');
                header("Location:mypage.php");
            }
        }catch(Exception $e){
            error_log('エラー発生:'.$e->getMessage());
            $err_msg['common'] = MSG07;
        }
    }
}
 debug('画面表示終了　☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆');
?>
<?php
$siteTitle = (!$edit_flg)? 'ねこ図鑑 - ねこちゃん登録':'ねこ図鑑 - ねこちゃん編集' ;
require('head.php');
?>
<body>
    <div class="title">
        <h1><?php echo (!$edit_flg)? 'ねこちゃんを登録する':'ねこちゃんの編集をする'; ?></h1>
         </div>
        
         <section class="form-wrap">
         <div class="form">
             <form action="" method="post" class="form3" enctype="multipart/form-data">
                 <div class="area-msg">
                     <?php
                     if(!empty($err_msg['common'])) echo $err_msg['common'];
                     ?>
                 </div>
                 <div class="area-msg">
                     <?php
                     if(!empty($err_msg['catname'])) echo $err_msg['catname'];
                     ?>
                 </div>
                  <label class="<?php if(!empty($err_msg['catname'])) echo 'err'; ?>">
                      名前<span class="label-requre">必須</span>
                 <input type="text" name="catname" value="<?php echo getFormData('catname'); ?>">
                  </label>
                  <label>
                      性別<br>
                  <input type="radio" name="gender" value="メス" <?php echo (getFormData('gender') == "メス")? "checked" : ""; ?>>メス
                  <input type="radio" name="gender" value="オス" <?php echo (getFormData('gender') == "オス")? "checked" : ""; ?>>オス
                  </label>
                  <div class="area-msg"></div>
                  <label>
                      生年月日
                 <input type="date" name="birth" value="<?php echo getFormData('birth'); ?>">
                  </label>
                 <div class="area-msg">
                     <?php
                     if(!empty($err_msg['type_id'])) echo $err_msg['type_id'];
                     ?>
                 </div>
                  <label class="<?php if(!empty($err_msg['catname'])) echo 'err'; ?>">
                      種類<span class="label-requre">必須</span>
                 <select name="type_id">
                     <option value="0" <?php if(getFormData('type_id') == 0){echo 'selected';} ?>>選択してください</option>
                     <?php
                     foreach($dbCategoryData as $key => $val){
                      ?>
                      <option value="<?php echo $val['id'] ?>" <?php if(getFormData('type_id') == $val['id']){ echo 'selected' ;} ?> >   
                      <?php 
                         echo $val['name']; 
                          //debug('$valの中身:'.print_r($val,true));
                         //debug('$dbCategoryDataの中身:'.print_r($dbCategoryData,true));
                          ?>
                      <?php
                     }
                     ?>
                 </select>
                  </label>
                   
                 
                     ねこちゃん写真<br>
                     <div class="mainImg">
                       <div class="area-msg">
                     <?php
                     if(!empty($err_msg['pic1'])) echo $err_msg['pic1'];
                     ?>
                 </div>
                        <label class="<?php if(!empty($err_msg['catname'])) echo 'err'; ?>">
                        メイン画像<span class="label-requre">必須</span>
                         <label class="area-drop <?php if(!empty($err_msg['pic1'])) echo 'err'; ?>" style="height:350px;line-height:350px;font-size:16px;">
                          
                           <input type="hidden" name="MAX_FILE_SIZE" value="3145728" class="<?php if(!empty($err_msg['catname'])) echo 'err'; ?>">
                           <input type="file" name="pic1" class="profpic" style="height:350px;line-height:350px;">                
                           <img src="<?php echo getFormData('pic1'); ?>" alt="" class="prev-img" style="<?php if(empty(getFormData('pic1'))) echo 'display:none;' ?>">
                              ドラッグ　＆　ドロップ
                         </label>
                    </label>

                               </div>
                               <div style="overflow:hidden">                         
                               <div class="imgDrop-container">
                               <label>
                       サブ画像1
                         <label class="area-drop <?php if(!empty($err_msg['pic2'])) echo 'err'; ?>">
                           <input type="hidden" name="MAX_FILE_SIZE" value="3145728">
                           <input type="file" name="pic2" class="profpic">                
                           <img src="<?php echo getFormData('pic2'); ?>" alt="" class="prev-img" style="<?php if(empty(getFormData('pic2'))) echo 'display:none;' ?>">
                              ドラッグ　＆　ドロップ
                         </label>
                              <div class="area-msg">
                                  <?php
                                    if(!empty($err_msg['pic2'])) echo $err_msg['pic2'];
                                ?>
                              </div>
                              </label>
                               </div>
                               <div class="imgDrop-container">
                              <label> 
                        サブ画像2
                         <label class="area-drop <?php if(!empty($err_msg['pic3'])) echo 'err'; ?>">
                           <input type="hidden" name="MAX_FILE_SIZE" value="3145728">
                           <input type="file" name="pic3" class="profpic">                
                           <img src="<?php echo getFormData('pic3'); ?>" alt="" class="prev-img" style="<?php if(empty(getFormData('pic3'))) echo 'display:none;' ?>">
                              ドラッグ　＆　ドロップ
                         </label>
                              <div class="area-msg">
                                  <?php
                                    if(!empty($err_msg['pic3'])) echo $err_msg['pic3'];
                                ?>
                              </div>
                              </label>
                               </div>
                               <div class="imgDrop-container">
                               <label>
                        サブ画像3
                         <label class="area-drop <?php if(!empty($err_msg['pic4'])) echo 'err'; ?>">
                           <input type="hidden" name="MAX_FILE_SIZE" value="3145728">
                           <input type="file" name="pic4" class="profpic">                
                           <img src="<?php echo getFormData('pic4'); ?>" alt="" class="prev-img" style="<?php if(empty(getFormData('pic4'))) echo 'display:none;' ?>">
                              ドラッグ　＆　ドロップ
                         </label>
                              <div class="area-msg">
                                  <?php
                                    if(!empty($err_msg['pic4'])) echo $err_msg['pic4'];
                                ?>
                              </div>
                              </label>
                               </div>
                     </div>
                  
                  <div class="area-msg">
                     <?php
                     if(!empty($err_msg['detail'])) echo $err_msg['detail'];
                     ?>
                 </div>
                  <label>
                      説明　※500文字以内
                      <textarea name="des" style="height:250px;" id="js-count"><?php echo getFormData('des'); ?></textarea>
                  </label>
                  <p class="counter-text"><span id="js-count-view">0</span>/500文字</p><br>
                  <input type="submit" value="<?php echo (!$edit_flg) ? '登録' : '更新' ; ?>" class="btn" name="<?php echo (!$edit_flg) ? 'submit' : '' ; ?>">
             </form>
         </div>
         
         <?php require('side.php'); ?>
         
    </section>
   
</body>

<?php
require('footer.php');
?>
