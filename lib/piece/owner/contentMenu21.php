			<div id="layout-body">
				<h2><?php echo _t('서브메뉴 : 링크관리')?></h2>
				
				<div id="sub-menu-box">
					<ul id="sub-menu">
						<li id="sub-menu-list"><a href="<?php echo $blogURL?>/owner/link"><span class="text"><?php echo _t('목록을 봅니다')?></span></a></li>
						<li id="sub-menu-add" class="selected"><a href="<?php echo $blogURL?>/owner/link/add"><span class="text"><?php echo _t('새 링크를 추가합니다')?></span></a></li>
						<li id="sub-menu-helper"><a href="http://www.tattertools.com/doc/12" onclick="window.open(this.href); return false;"><span class="text"><?php echo _t('도우미')?></span></a></li>
					</ul>
				</div>
				
				<hr class="hidden" />
				
				<div id="psuedo-box">
					<div id="data-outbox">
