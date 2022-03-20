<?php

require('function.php');

debug('☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆');
debug('☆　トップページ ');
debug('☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆');
debugLogStart();



$currentPageNum = (!empty($_GET['p'])) ? $_GET['p'] : 1;

$type = (!empty($_GET['type_id']))? $_GET['type_id'] : '';
debug('$typeの中身：'.print_r($type,true));

if(!is_int((int)$currentPageNum)){
    error_log('エラー発生：指定ページに不正な値が入りました');
    header("Location:index.php");
}
$listSpan = 10;

$currentMinNum = (($currentPageNum-1)*$listSpan);//1ページ目は０、２ページ目は１０、３ページ目は２０・・

$sort = (!empty($_GET['sort'])) ? $_GET['sort'] : '';

$dbCatsData = getNekoList($currentMinNum,$type,$sort);
debug('$dbCatsDataの中身：'.print_r($dbCatsData,true));

$dbCategoryData = getCategory();




    
//debug('$dbCategoryDataの中身：'.print_r($dbCategoryData,true));
//debug('$dbCatsDataの中身：'.print_r($dbCatsData,true));


debug('画面表示処理終了　☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆☆');

$siteTitle= 'ねこ図鑑 - トップページ';
require('head.php');
?>
     <body>
       <div class="main">
       <div class="title">
        <h1>
         <i class="fas fa-paw topicn"></i> 
        <a href="index.php">ねこ図鑑</a>
         <i class="fas fa-paw topicn"></i> 
        </h1>
        
         </div>
             <div class="right">
                <nav id="side-nav" >
                <ul>
                    <?php
                    if(empty($_SESSION['user_id'])){
                     ?>
                     <li><a href="signup.php">ユーザー登録</a></li>
                     <li><a href="login.php" class="btn2">ログイン</a></li> 
                     <?php
                    }else{
                        ?>
                        <li><a href="mypage.php">マイページ</a></li>
                    <li><a href=logout.php>ログアウト</a></li>
                    <?php
                    }
                    ?>
                </ul>
                </nav>
            
                <div class="sort">
                <p>種類</p>
               
                 <form method="get"  class="selectform">
                    <div>
                   <select name="type_id">
                   <option value="0" <?php if(getFormData('type_id',true) == 0){echo 'selected'; } ?> >選択してください</option><!--//$_GET[n_id] = 0　の場合 -->
                    <?php 
                    foreach($dbCategoryData as $key => $val){
                    ?>
                        <option value="<?php echo $val['id']; ?>" <?php if(getFormData('type_id',true) == $val['id']){echo 'selected' ;}?>><?php echo $val['name']." (".countType($val['id']).")" ; ?></option>
                        
                    <?php
                         }
                    //debug('$dbCategoryDataの中身:'.print_r($dbCategoryData,true));
                    ?>
                    
                    
                    </select>
                    </div>
                     <div>
                     <p>表示順</p>
                     <select name="sort">
                         <option value="0" <?php if(getFormData('sort',true)==0){echo 'selected';} ?>>選択してください</option>
                         <option value="1" <?php if(getFormData('sort',true)==1){echo 'selected';} ?>>新規登録順</option>
                         <option value="2" <?php if(getFormData('sort',true)==2){echo 'selected';} ?>>登録順</option>
                     </select>
                     </div>
                    <input type="submit" value="検索" class="btn">
                    </form>
                    
                 </div>
                 
             </div>
             <div class="left top">
        
                  <?php
                   if($dbCatsData['data']):
                   foreach($dbCatsData['data'] as $key => $val):
                    //debug('$dbCatsDataの中身:'.print_r($dbCatsData,true));
                 //年齢算出
                 $birthdate = $val['birth'];
                 $now = date("Ymd");
                 $birthday = str_replace("-", "", $birthdate);
    
                ?>
             
               <div class="zukan-wrap">
                <div class="zukan">
                 <img src="<?php echo sanitize($val['pic1']); ?>" class="z-left l-img" style=width:400px;>
                 <div style="overflow:hidden;min-height: 250px;">
                 <div class="z-right title-box1">
                 <div class="title-box1-title">プロフィール</div>
                 <span>種類　: <?php if(!empty($val['name'])) echo sanitize($val['name']).'<br>'; ?></span>
                     <span>名前　: <?php echo sanitize($val['catname']).'<br>'; ?></span>
                 <span><?php if(!empty($val['gender'])) echo '性別　: '.sanitize($val['gender']).'<br>'; ?></span>
                 <span><?php if($val['birth'] !== '0000-00-00')  echo '年齢　: '.floor(($now-$birthday)/10000).'歳'.'<br>'; ?></span>
                 </div>
                  <div style="width:100%;text-align: center; float: right;">
                   <a href="nekoDetail.php<?php echo (!empty(appendGetParam())) ? appendGetParam().'&n_id='.$val['catid'] : '?n_id='.$val['catid']; ?>" class="detail">詳しく見る</a>
                    </div>
                    </div>
             </div>
             </div>
             <?php
                 endforeach;
                endif;
                   ?>
         
         </div>
         <div>
         
         <?php pagination($currentPageNum,$dbCatsData['total_page']); ?>
         </div>
         
         <div class="search-right">
             <span class="num"><?php echo (!empty($dbCatsData['data'])) ? $currentMinNum+1 : 0; ?><span class="num"> - </span>
             <?php echo $currentMinNum+count($dbCatsData['data']); ?></span>件 / <span class="num"><?php echo sanitize($dbCatsData['total']); ?></span>件中
         </div>
        
       </div>
       <?php
         require('footer.php');
        ?>