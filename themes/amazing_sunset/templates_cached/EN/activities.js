"use strict";if(typeof window.latest_activity=='undefined')
{window.latest_activity=0;window.s_ajax_update_locking=0;window.activities_feed_grow=true;}
function s_update_get_data()
{if((++window.s_ajax_update_locking)>1)
{window.s_ajax_update_locking=1;}else
{jQuery.ajax({url:'https://www.lovinity.org/data_custom/latest_activity.txt?chrome_fix='+Math.floor(Math.random()*10000),data:{},success:function(data,status){if(window.parseInt(data)!=window.latest_activity)
{window.latest_activity=window.parseInt(data);var url='https://www.lovinity.org/data_custom/activities_updater.php'+keep_stub(true);var list_elements=$('li','#activities_feed');var last_id=((typeof list_elements.attr('id')=='undefined')?'-1':list_elements.attr('id').replace(/^activity_/,''));var post_val='last_id='+last_id+'&mode='+window.activities_mode;if((window.activities_member_ids!==null)&&(window.activities_member_ids!==''))
post_val=post_val+'&member_ids='+window.activities_member_ids;post_val+='&csrf_token='+window.encodeURIComponent(get_csrf_token());jQuery.ajax({url:url.replace(/^https?:/,window.location.protocol),type:'POST',data:post_val,cache:false,timeout:5000,success:function(data,stat){s_update_show(data,stat);},error:function(a,stat,err){s_update_show(err,stat);}});}else
{window.s_ajax_update_locking=0;}},dataType:'text'});}}
function s_update_show(data,stat)
{if(window.s_ajax_update_locking>1)
{window.s_ajax_update_locking=1;}else
{var succeeded=false;if(stat=='success')
{if($('success',data).text()=='1')
{var list_elements=$('li','#activities_feed');var list_items=$('listitem',data);list_elements.removeAttr('toFade');var top_of_list=document.getElementById('activities_holder').firstChild;jQuery.each(list_items,function(){var this_li=document.createElement('li');this_li.id='activity_'+$(this).attr('id');this_li.className='activities_box box';this_li.setAttribute('toFade','yes');top_of_list.parentNode.insertBefore(this_li,top_of_list);set_inner_html(this_li,Base64.decode($(this).text()));});var no_messages=document.getElementById('activity_-1');if(no_messages)no_messages.style.display='none';list_elements=$('li','#activities_feed');if((!window.activities_feed_grow)&&(list_elements.length>window.activities_feed_max))
{for(var i=window.activities_feed_max;i<list_elements.length;i++)
{list_elements.last().remove();}}
$('#activities_general_notify').text('');$('li[toFade="yes"]','#activities_feed').hide().fadeIn(1200);succeeded=true;}else
{if($('success',data).text()=='2')
{$('#activities_general_notify').text('');succeeded=true;}}}
if(!succeeded)
{$('#activities_general_notify').text('Internal error');}
window.s_ajax_update_locking=0;}}
function s_update_remove(event,id)
{window.fauxmodal_confirm('This will permanently delete the message.',function(result)
{if(result)
{var url='https://www.lovinity.org/data_custom/activities_removal.php'+keep_stub(true);var post_val='removal_id='+id;post_val+='&csrf_token='+window.encodeURIComponent(get_csrf_token());jQuery.ajax({url:url.replace(/^https?:/,window.location.protocol),type:'POST',data:post_val,cache:false,timeout:5000,success:function(data,stat){s_update_remove_show(data,stat);},error:function(a,stat,err){s_update_remove_show(err,stat);}});}});event.preventDefault();}
function s_update_remove_show(data,stat)
{var succeeded=false;var status_id='';var animation_speed=1600;if(stat=='success')
{if($('success',data).text()=='1')
{status_id='#activity_'+$('status_id',data).text();$('.activities_content',status_id,'#activities_feed').text($('feedback',data).text()).addClass('activities_content__remove_success').hide().fadeIn(animation_speed,function(){$(status_id,'#activities_feed').fadeOut(animation_speed,function(){$(status_id,'#activities_feed').remove();});});}else
{switch($('err',data).text())
{case'perms':status_id='#activity_'+$('status_id',data).text();var backup_up_text=$('activities_content',status_id,'#activities_feed').text();$('.activities_content',status_id,'#activities_feed').text($('feedback',data).text()).addClass('activities_content__remove_failure').hide().fadeIn(animation_speed,function(){$('.activities_content',status_id,'#activities_feed').fadeOut(animation_speed,function(){$('.activities_content',status_id,'#activities_feed').text(backup_up_text).removeClass('activities_content__remove_failure').fadeIn(animation_speed);});});break;case'missing':default:break;}}}}