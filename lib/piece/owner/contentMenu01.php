			<div id="layout-body">
				<h2><?php echo _t('서브메뉴 : 글관리');?></h2>
				
				<div id="sub-menu-box">
					<ul id="sub-menu">
						<li id="sub-menu-add"><a href="<?php echo $blogURL;?>/owner/entry/post" onclick="window.location.href = '<?php echo $blogURL;?>/owner/entry/post'<?php echo (getDraftEntryId() ? "+(confirm('" . _t('임시 저장본을 보시겠습니까?') . "') ? '?draft' : '')" : '');?>"><span class="text"><?php echo _t('글쓰기');?></span></a></li>
						<li id="sub-menu-list"><a href="<?php echo $blogURL;?>/owner/entry"><span class="text"><?php echo _t('글 목록');?></span></a></li>
						<li id="sub-menu-thread" class="selected"><a href="<?php echo $blogURL;?>/owner/entry/comment"><span class="text"><?php echo _t('댓글');?></span></a></li>
						<li id="sub-menu-notify"><a href="<?php echo $blogURL;?>/owner/entry/notify"><span class="text"><?php echo _t('댓글 알리미');?></span></a></li>
						<li id="sub-menu-trackback"><a href="<?php echo $blogURL;?>/owner/entry/trackback"><span class="text"><?php echo _t('글걸기');?></span></a></li>
						<li id="sub-menu-category"><a href="<?php echo $blogURL;?>/owner/entry/category"><span class="text"><?php echo _t('분류 관리');?></span></a></li>
						<li id="sub-menu-trash"><a href="<?php echo $blogURL;?>/owner/entry/trash"><span class="text"><?php echo _t('휴지통');?></span></a></li>
						<li id="sub-menu-helper"><a href="http://www.tattertools.com/doc/5" onclick="window.open(this.href); return false;"><span class="text"><?php echo _t('도우미');?></span></a></li>
					</ul>
				</div>
				
				<hr class="hidden" />
				
				<div id="pseudo-box">
					<div id="data-outbox">
