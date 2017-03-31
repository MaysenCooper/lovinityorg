"use strict";function facebook_init(app_id,channel_url,just_logged_out,serverside_fbuid,home_page_url,logout_page_url)
{window.fbAsyncInit=function(){FB.init({appId:app_id,channelUrl:channel_url,status:true,cookie:true,xfbml:true});};(function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(d.getElementById(id))return;js=d.createElement(s);js.id=id;js.src='//connect.facebook.net/en_US/all.js#xfbml=1&appId=760567120668325';fjs.parentNode.insertBefore(js,fjs);}(document,'script','facebook-jssdk'));}
function facebook_trigger_refresh(home_page_url)
{window.setTimeout(function(){if((window.location.href.indexOf('login')!=-1)&&(window==window.top))
{window.location=home_page_url;}else
{var current_url=window.top.location.href;if(current_url.indexOf('refreshed_once=1')==-1)
{current_url+=((current_url.indexOf('?')==-1)?'?':'&')+'refreshed_once=1';window.top.location=current_url;}
else if(current_url.indexOf('keep_refreshed_once=1')==-1)
{window.location+='&keep_refreshed_once=1';}}},500);}