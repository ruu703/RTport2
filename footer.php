    <footer id="footer">
        <div>Copyright ruu. All Rights Reserved.</div>     
    </footer>
        <script src="js/vendor/jquery-2.2.2.min.js"></script>
        <script>
        $(function(){
            
                var $dropArea = $('.area-drop');
                var $fileInput = $('.profpic');
                $dropArea.on('dragover',function(e){
                    e.stopPropagation();
                    e.preventDefault();
                    $(this).css('border','3px #ccc dashed');
                });
                $dropArea.on('dragleave',function(e){
                    e.stopPropagation();
                    e.preventDefault();
                    $(this).css('border','none');
                });
                $fileInput.on('change',function(e){
                    $dropArea.css('border','none');
                    var file = this.files[0],
                        $img = $(this).siblings('.prev-img'),
                        fileReader = new FileReader();
                    fileReader.onload = function(event){
                        $img.attr('src',event.target.result).show();
                    };
                    fileReader.readAsDataURL(file);
                });
        
                
                // テキストエリアカウント
        var $countUp = $('#js-count'),
            $countView = $('#js-count-view');
        $countUp.on('keyup', function(e){
        $countView.html($(this).val().length);
        });
            //メッセージ表示
            var $jsShowMsg = $('#js-show-msg');
        var msg = $jsShowMsg.text();
        if(msg.replace(/^[\s　]+|[\s　]+$/g, "").length){//中に文字があれば実行する
        $jsShowMsg.slideToggle('slow');
        setTimeout(function(){ $jsShowMsg.slideToggle('slow'); }, 5000);
        }
            //画像切替
            var $switchImgSubs = $('.js-switch-img-sub'),
            $switchImgMain = $('#js-switch-img-main');
        $switchImgSubs.on('click',function(e){
            $switchImgMain.attr('src',$(this).attr('src'));
            });
        
            //お気に入り登録・削除
            var $like,
                likeCatId;
            $like = $('.js-click-like') || null;
            likeCatId = $like.data('catid') || null;
            if(likeCatId !== undefined && likeCatId !== null){
                $like.on('click',function(){
                    var $this = $(this);
                    $.ajax({
                        type:"POST",
                        url:"ajaxLike.php",
                        data:{catId : likeCatId}
                    }).done(function(data){
                            console.log('Ajax Success');
                        $this.toggleClass('active');
                            }).fail(function(msg){
                        console.log('Ajax Error');
                    });
                });
            }
                });
        </script>
    </body>
 </html>