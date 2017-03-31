"use strict";function s_update_focus(event)
{if($(this).val().trim()=='Type what\'s on your mind...')
{$(this).val('');this.className=this.className.replace(/ field_input_non_filled/g,' field_input_filled');}
$(this).removeClass('fade_input');}
function s_update_blur(event)
{if($(this).val().trim()=='')
{$(this).val('Type what\'s on your mind...');this.className=this.className.replace(/ field_input_filled/g,' field_input_non_filled');}
$(this).addClass('fade_input');}
function s_maintain_char_count(event)
{var char_count=$('#activity_status').val().length;if(char_count<255)
$('#activities_update_notify','#status_updates').attr('class','update_success').text((254-char_count)+' characters left');else
$('#activities_update_notify','#status_updates').attr('class','update_error').text((char_count-254)+' characters too many');}
function s_update_submit(event)
{var subject_text='';if(event)
{event.preventDefault();subject_text=$('textarea',this).val().trim();}else
{subject_text=$('textarea','#fp_status_form').val().trim();}
if((subject_text=='Type what\'s on your mind...')||(subject_text==''))
{$('#activities_update_notify','#status_updates').attr('class','update_error').text('Please enter a status before updating.');}else
{var url='https://www.lovinity.org/data_custom/activities_handler.php'+keep_stub(true);jQuery.ajax({url:url.replace(/^https?:/,window.location.protocol),type:'POST',data:$('#fp_status_form').serialize(),cache:false,timeout:5000,success:function(data,stat){s_update_retrieve(data,stat);},error:function(a,stat,err){s_update_retrieve(err,stat);}});}}
function s_update_retrieve(data,tStat)
{document.getElementById('button').disabled=false;var update_box=$('#activities_update_notify','#status_updates');if(tStat=='success')
{if($('success',data).text()=='0')
{if($('feedback',data).text().substr(0,13)=='You must be logged-in to perform this action.')
{window.fauxmodal_alert('You must be logged-in to perform this action.');}else
{update_box.attr('class','update_error').html($('feedback',data).text());}}
else if($('success',data).text()=='1')
{update_box.attr('class','update_success').text($('feedback',data).text());if($('#activities_feed').length!=0)
{s_update_get_data();}
update_box.fadeIn(1200,function(){update_box.fadeOut(1200,function(){var as=$('#activity_status');update_box.attr('class','update_success').text('254 characters left');update_box.fadeIn(1200);as.parent().height(as.parent().height());as.val('Type what\'s on your mind...');as[0].className=as[0].className.replace(/ field_input_filled/g,' field_input_non_filled');as.fadeIn(1200,function(){as.parent().height('');});});});}}else
{var errText='Something went wrong. If using Comcode, check it is correct.';update_box.attr('class','').addClass('update_error').text(errText);update_box.hide();update_box.fadeIn(1200,function(){update_box.delay(2400).fadeOut(1200,function(){s_maintain_char_count(null);update_box.fadeIn(1200);});});}}