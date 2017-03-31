<?php

require_code("galleries");
return '<div class="media_box">



	<figure>
		
	

	

	
		
	

	<meta itemprop="width" content="560">
	<meta itemprop="height" content="30">
	
		<meta itemprop="duration" content="T14400S">
	
	<meta itemprop="thumbnailURL" content="">
	<meta itemprop="embedURL" content="http://149.56.30.127:8000/stream/2/;.mp3">

	<div id="player_1796089232_wrapper" style="position: relative; display: block; width: 560px; height: 30px;"><object type="application/x-shockwave-flash" data="https://lovinity.org/data/jwplayer.flash.swf?rand=1916644807" width="100%" height="100%" bgcolor="#000000" id="player_1796089232" name="player_1796089232" tabindex="0" title="Adobe Flash Player"><param name="allowfullscreen" value="true"><param name="allowscriptaccess" value="always"><param name="seamlesstabbing" value="true"><param name="wmode" value="transparent"></object><div id="player_1796089232_aspect" style="display: none;"></div><div id="player_1796089232_jwpsrv" style="position: absolute; top: 0px; z-index: 10;"><div class="afs_ads" style="width: 1px; height: 1px; position: absolute; background: transparent;">&nbsp;</div></div></div>

	

	<script>// <![CDATA[
		
		add_event_listener_abstract(window,\'load\',function() {
			jwplayer(\'player_1796089232\').setup({
				width: 560,
				height: 30,
				autostart: false,
				
					duration: 14400,
				
				file: \'http://149.56.30.127:8000/stream/1/;.mp3\',
				type: \'mp3\',
				image: \'\',
				flashplayer: \'https://lovinity.org/data/jwplayer.flash.swf?rand=1916644807\',
				events: {
					
					onComplete: function() { if (document.getElementById(\'next_slide\')) player_stopped(); },
					onReady: function() { if (document.getElementById(\'next_slide\')) { stop_slideshow_timer(); jwplayer(\'player_1796089232\').play(true); } }
				}
			});
		});
	//]]></script>

	

	

	</figure>


</div>';
