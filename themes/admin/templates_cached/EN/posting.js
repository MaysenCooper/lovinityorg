"use strict";require_javascript('ajax',window.do_ajax_request);function add_attachment(start_num,posting_field_name)
{if(typeof window.num_attachments=='undefined')return;if(typeof window.max_attachments=='undefined')return;var add_to=document.getElementById('attachment_store');window.num_attachments++;if(window.attachment_template.replace(/\s/,'')!='')
{var new_div=document.createElement('div');set_inner_html(new_div,window.attachment_template.replace(/\_\_num_attachments\_\_/g,window.num_attachments));add_to.appendChild(new_div);}
if(typeof window.rebuild_attachment_button_for_next!='undefined')
{rebuild_attachment_button_for_next(posting_field_name);}
var element=document.getElementById('file'+window.num_attachments);if(element)element.setAttribute('unselectable','on');if(typeof window.trigger_resize!='undefined')trigger_resize();}
function attachment_present(post_value,number)
{return!(post_value.indexOf('[attachment]new_'+number+'[/attachment]')==-1)&&(post_value.indexOf('[attachment_safe]new_'+number+'[/attachment_safe]')==-1)&&(post_value.indexOf('[attachment thumb="1"]new_'+number+'[/attachment]')==-1)&&(post_value.indexOf('[attachment_safe thumb="1"]new_'+number+'[/attachment_safe]')==-1)&&(post_value.indexOf('[attachment thumb="0"]new_'+number+'[/attachment]')==-1)&&(post_value.indexOf('[attachment_safe thumb="0"]new_'+number+'[/attachment_safe]')==-1);}
function set_attachment(field_name,number,filename,multi,uploader_settings)
{if(typeof multi=='undefined')multi=false;if(typeof window.insert_textbox=='undefined')return;if(typeof window.num_attachments=='undefined')return;if(typeof window.max_attachments=='undefined')return;var post=document.getElementById(field_name);post=ensure_true_id(post,field_name);var tmp_form=post.form;if((tmp_form)&&(tmp_form.preview))
{tmp_form.preview.checked=false;tmp_form.preview.disabled=true;}
var post_value=get_textbox(post);var done=attachment_present(post.value,number)||attachment_present(post_value,number);if(!done)
{var filepath=filename;if((!filename)&&(document.getElementById('file'+number)))
{filepath=document.getElementById('file'+number).value;}
if(filepath=='')
return;var ext=filepath.replace(/^.*\./,'').toLowerCase();var is_image=(',jpg,jpeg,gif,png,ico,'.indexOf(','+ext+',')!=-1);var is_video=(',3gp,3g2,3gp2,3gpp,3p,asf,avi,flv,f4v,mov,mpa,mp4,mpe,mpeg,mpg,m2v,mpv2,mp2,m4v,qt,ram,rm,wmv,ogg,ogv,webm,pdf,'.indexOf(','+ext+',')!=-1);var is_audio=(',ra,mid,mp3,wav,wma,aif,aifc,aiff,'.indexOf(','+ext+',')!=-1);var is_archive=(ext=='tar')||(ext=='zip');var prefix='',suffix='';if(multi&&is_image)
{prefix='[media_set]\n';suffix='[/media_set]';}
var tag='attachment';var show_overlay,defaults={};if(filepath.indexOf('fakepath')==-1)
defaults.description=filepath;defaults.thumb='1';defaults.type='';show_overlay=true;if(multi||is_archive)
{show_overlay=false;}
if(is_image)
{tag='attachment_safe';}
if(multi||is_image)
{defaults.framed='0';}
if(!show_overlay)
{var comcode='['+tag;for(var key in defaults)
{comcode+=' '+key+'="'+(defaults[key].replace(/"/g,'\\"'))+'"';}
comcode+=']new_'+number+'[/'+tag+']';if(prefix!='')insert_textbox(post,prefix);if(multi)
{var split_filename=document.getElementById('txtFileName_file'+window.num_attachments).value.split(/:/);for(var i=0;i<split_filename.length;i++)
{if(i!=0)window.num_attachments++;var new_comcode=comcode.replace(']new_'+number+'[',']new_'+window.num_attachments+'[');if(split_filename[i].indexOf('fakepath')==-1)
{new_comcode=new_comcode.replace(' description="'+defaults.description.replace(/"/g,'\\"')+'"',' description="'+split_filename[i].replace(/"/g,'\\"')+'"');}
insert_textbox(post,new_comcode);}
number=''+(window.parseInt(number)+split_filename.length-1);}else
{insert_textbox(post,comcode,document.selection?document.selection:null);}
if(suffix!='')insert_textbox(post,suffix);var add_another_field=(number==window.num_attachments)&&(window.num_attachments<window.max_attachments);if(add_another_field)
{add_attachment(window.num_attachments+1,field_name);}
if(typeof uploader_settings!='undefined')
{uploader_settings.callbacks.push(function(){if(is_wysiwyg_field(post))
{generate_background_preview(post);}});}
return;}
var wysiwyg=is_wysiwyg_field(post);if((typeof window.event!='undefined')&&(window.event))window.event.returnValue=false;var url='https://www.lovinity.org/data/comcode_helper.php';url+='?field_name='+field_name;url+='&type=step2';url+='&tag='+tag;url+='&default=new_'+number;url+='&is_image='+(is_image?'1':'0');url+='&is_archive='+(is_archive?'1':'0');url+='&multi='+(multi?'1':'0');url+='&prefix='+prefix;if(wysiwyg)url+='&in_wysiwyg=1';for(var key in defaults)
{url+='&default_'+key+'='+window.encodeURIComponent(defaults[key]);}
url+=keep_stub();window.setTimeout(function(){window.faux_showModalDialog(maintain_theme_in_link(url),'','width=750,height=auto,status=no,resizable=yes,scrollbars=yes,unadorned=yes',function(comcode_added)
{if(comcode_added)
{if(multi)
{var comcode_semihtml='',comcode='';var split_filename=document.getElementById('txtFileName_file'+window.num_attachments).value.split(/:/);for(var i=1;i<split_filename.length;i++)
{window.num_attachments++;var tmp=window.insert_comcode_tag([']new_'+number+'[',' description="'+defaults.description.replace(/"/g,'\\"')+'"'],[']new_'+window.num_attachments+'[',' description="'+((filepath.indexOf('fakepath')==-1)?filepath:defaults.description).replace(/"/g,'\\"')+'"'],true);comcode_semihtml+=tmp[0];comcode+=tmp[1];}
number=''+(window.parseInt(number)+split_filename.length-1);if(suffix!='')
{comcode+=suffix;comcode_semihtml+=suffix;}
insert_textbox(post,comcode,null,true,comcode_semihtml);}
var add_another_field=(number==window.num_attachments)&&(window.num_attachments<window.max_attachments);if(add_another_field)
{add_attachment(window.num_attachments+1,field_name);}
if((comcode_added.indexOf('[attachment_safe')!=-1)&&(is_wysiwyg_field(post)))
{generate_background_preview(post);}}else
{var clear_button=document.getElementById('fsClear_file'+number);if(clear_button)
{clear_button.onclick();}}});},800);}else
{var add_another_field=(number==window.num_attachments)&&(window.num_attachments<window.max_attachments);if(add_another_field)
add_attachment(window.num_attachments+1,field_name);}}
function generate_background_preview(post)
{var form_post='';var form=post.form;for(var i=0;i<form.elements.length;i++)
{if((!form.elements[i].disabled)&&(typeof form.elements[i].name!='undefined')&&(form.elements[i].name!=''))
{var name=form.elements[i].name;var value=clever_find_value(form,form.elements[i]);if(name=='title'&&value=='')value='x';form_post+='&'+name+'='+window.encodeURIComponent(value);}}
form_post=modsecurity_workaround_ajax(form_post.substr(1));var preview_ret=do_ajax_request(window.form_preview_url+'&js_only=1&known_utf8=1',null,form_post);eval(preview_ret.responseText.replace('<script>','').replace('</script>',''));}
function do_input_html(field_name)
{if(typeof window.insert_textbox_wrapping=='undefined')return;var post=document.getElementById(field_name);post=ensure_true_id(post,field_name);insert_textbox_wrapping(post,'semihtml','');}
function do_input_code(field_name)
{if(typeof window.insert_textbox_wrapping=='undefined')return;var post=document.getElementById(field_name);post=ensure_true_id(post,field_name);insert_textbox_wrapping(post,(post.name=='message')?'tt':'codebox','');}
function do_input_quote(field_name)
{if(typeof window.insert_textbox_wrapping=='undefined')return;var post=document.getElementById(field_name);post=ensure_true_id(post,field_name);window.fauxmodal_prompt('Enter the quote source (e.g. a person\'s name), or leave blank for a sourceless quote.','',function(va)
{if(va!==null)insert_textbox_wrapping(post,'[quote=\"'+va+'\"]','[/quote]');},'Insert a Comcode quote tag');}
function do_input_box(field_name)
{if(typeof window.insert_textbox_wrapping=='undefined')return;var post=document.getElementById(field_name);post=ensure_true_id(post,field_name);window.fauxmodal_prompt('Enter the box title','',function(va)
{if(va!==null)insert_textbox_wrapping(post,'[box=\"'+va+'\"]','[/box]');},'Insert a Comcode box tag (puts a box around some content)');}
function do_input_menu(field_name)
{if(typeof window.insert_textbox=='undefined')return;window.fauxmodal_prompt('Enter the name of the menu you would like to use. The following menus are defined: \'+(document.getElementById(field_name).form.menu_items.value)+\'','',function(va)
{if(va)
{window.fauxmodal_prompt('Enter the caption you would like to use for the menu','',function(vb)
{if(!vb)vb='';var add;var element=document.getElementById(field_name);element=ensure_true_id(element,field_name);add='[block=\""+escape_comcode(va)+"\" caption=\""+escape_comcode(vb)+"\" type=\"tree\"]menu[/block]';insert_textbox(element,add);},'Add a stored menu');}},'Add a stored menu');}
function do_input_block(field_name)
{if((typeof window.event!='undefined')&&(window.event))window.event.returnValue=false;var url='https://www.lovinity.org/data/block_helper.php?field_name='+field_name+keep_stub();url=url+'&block_type='+(((field_name.indexOf('edit_panel_')==-1)&&(window.location.href.indexOf(':panel_')==-1))?'main':'side');window.faux_open(maintain_theme_in_link(url),'','width=750,height=auto,status=no,resizable=yes,scrollbars=yes',null,'Cancel');}
function do_input_comcode(field_name,tag)
{var attributes={};var default_embed=null;var save_to_id=null;if(tag==null)
{var element=document.getElementById(field_name);if(is_wysiwyg_field(element))
{var selection=window.wysiwyg_editors[field_name].getSelection();var ranges=selection.getRanges();if(typeof ranges[0]!='undefined')
{var comcode_element=ranges[0].startContainer.$;do
{var matches=comcode_element.nodeName.toLowerCase().match(/^comcode-(\w+)/);if(matches!==null)
{tag=matches[1];for(var i=0;i<comcode_element.attributes.length;i++)
{if(comcode_element.attributes[i].name!='id')
{attributes[comcode_element.attributes[i].name]=comcode_element.attributes[i].value;}}
default_embed=get_inner_html(comcode_element);if(comcode_element.id=='')
{comcode_element.id='comcode_'+Date.now();}
save_to_id=comcode_element.id;break;}
comcode_element=comcode_element.parentNode;}
while(comcode_element!==null);}}}
if((typeof window.event!='undefined')&&(window.event))window.event.returnValue=false;var url='https://www.lovinity.org/data/comcode_helper.php?field_name='+window.encodeURIComponent(field_name);if(tag)
{url+='&tag='+window.encodeURIComponent(tag);}
if(default_embed!==null)
{url+='&type=replace';}else
{if(tag==null)
{url+='&type=step1';}else{url+='&type=step2';}}
if(is_wysiwyg_field(document.getElementById(field_name)))url+='&in_wysiwyg=1';for(var key in attributes)
{url+='&default_'+key+'='+window.encodeURIComponent(attributes[key]);}
if(default_embed!==null)
{url+='&default='+window.encodeURIComponent(default_embed);}
if(save_to_id!==null)
{url+='&save_to_id='+window.encodeURIComponent(save_to_id);}
url+=keep_stub();window.faux_open(maintain_theme_in_link(url),'','width=750,height=auto,status=no,resizable=yes,scrollbars=yes',null,'Cancel');}
function do_input_list(field_name,add)
{if(typeof window.insert_textbox=='undefined')return;if(typeof add=='undefined')add=[];var post=document.getElementById(field_name);post=ensure_true_id(post,field_name);insert_textbox(post,'\n');window.fauxmodal_prompt('Enter the next list entry (or press enter or cancel to finish list)','',function(va)
{if((va!=null)&&(va!=''))
{add.push(va);return do_input_list(field_name,add)}
if(add.length==0)return;var i;if(post.value.indexOf('[semihtml')!=-1)
insert_textbox(post,'[list]\n');for(i=0;i<add.length;i++)
{if(post.value.indexOf('[semihtml')!=-1)
{insert_textbox(post,'[*]'+add[i]+'\n')}else
{insert_textbox(post,' - '+add[i]+'\n')}}
if(post.value.indexOf('[semihtml')!=-1)
insert_textbox(post,'[/list]\n')},'Insert a list');}
function do_input_hide(field_name)
{if(typeof window.insert_textbox=='undefined')return;window.fauxmodal_prompt('Enter the warning to be shown in place of the text','',function(va)
{if(va)
{window.fauxmodal_prompt('Enter the text that is only shown if you choose to view it','',function(vb)
{var element=document.getElementById(field_name);element=ensure_true_id(element,field_name);if(vb)
{insert_textbox(element,'[hide=\"'+escape_comcode(va)+'\"]'+escape_comcode(vb)+'[/hide]');}},'Insert a Comcode hide (spoiler) tag');}},'Insert a Comcode hide (spoiler) tag');}
function do_input_thumb(field_name,va)
{if(typeof window.insert_textbox=='undefined')return;if(typeof window.start_simplified_upload!='undefined'&&document.getElementById(field_name).name!='message')
{var test=start_simplified_upload(field_name);if(test)return;}
window.fauxmodal_prompt('Enter the URL',va,function(va)
{if((va!=null)&&(va.indexOf('://')==-1))
{window.fauxmodal_alert('This was not a valid URL (URLs contain "://")',function(){do_input_url(field_name,va);});return;}
if(va)
{generate_question_ui('Would you like the image to be shown full-size?',{buttons__thumbnail:'Thumbnail',buttons__fullsize:'Image'},'Insert a Comcode image tag',null,function(vb)
{window.fauxmodal_prompt('Enter the caption you would like to use for the image. You can leave this blank, but for accessibility you can specify an appropriate summary for the image content.','',function(vc)
{if(!vc)vc='';var element=document.getElementById(field_name);element=ensure_true_id(element,field_name);if(vb.toLowerCase()=='Image'.toLowerCase())
{insert_textbox(element,'[img=\"'+escape_comcode(vc)+'\"]'+escape_comcode(va)+'[/img]');}else
{insert_textbox(element,'[thumb caption=\"'+escape_comcode(vc)+'\"]'+escape_comcode(va)+'[/thumb]');}},'Insert a Comcode image tag');});}},'Insert a Comcode image tag');}
function do_input_attachment(field_name)
{if(typeof window.insert_textbox=='undefined')return;window.fauxmodal_prompt('Enter the attachment number (the new attachment you want to be placed at this position)','',function(va)
{if(!is_integer(va))
{window.fauxmodal_alert('This is not a valid attachment number');}else
{var element=document.getElementById(field_name);element=ensure_true_id(element,field_name);insert_textbox(element,'[attachment]new_'+va+'[/attachment]');}},'Add an attachment');}
function do_input_url(field_name,va)
{if(typeof window.insert_textbox=='undefined')return;window.fauxmodal_prompt('Enter the URL',va,function(va)
{if((va!=null)&&(va.indexOf('://')==-1))
{window.fauxmodal_alert('This was not a valid URL (URLs contain "://")',function(){do_input_url(field_name,va);});return;}
if(va!==null)
{window.fauxmodal_prompt('Enter the link name','',function(vb)
{var element=document.getElementById(field_name);element=ensure_true_id(element,field_name);if(vb!=null)insert_textbox(element,'[url=\"'+escape_comcode(vb)+'\"]'+escape_comcode(va)+'[/url]');},'Insert a Comcode URL tag');}},'Insert a Comcode URL tag');}
function do_input_page(field_name)
{if(typeof window.insert_textbox=='undefined')return;var result;if(typeof window.showModalDialog!='undefined'||true)
{window.faux_showModalDialog(maintain_theme_in_link('https://www.lovinity.org/data/page_link_chooser.php'+keep_stub(true)),null,'dialogWidth=600;dialogHeight=400;status=no;unadorned=yes',function(result)
{if((typeof result=='undefined')||(result===null))return;window.fauxmodal_prompt('Enter the caption','',function(vc)
{_do_input_page(field_name,result,vc);},'Add a page-link');});}else
{window.fauxmodal_prompt('Enter the zone codename that the page is in','',function(va)
{if(va!==null)
{window.fauxmodal_prompt('Enter the page codename','',function(vb)
{if(vb!==null)
{result=va+':'+vb;window.fauxmodal_prompt('Enter the caption','',function(vc)
{_do_input_page(field_name,result,vc);},'Add a page-link');}});}},'Add a page-link');}}
function _do_input_page(field_name,result,vc)
{var element=document.getElementById(field_name);element=ensure_true_id(element,field_name);insert_textbox(element,'[page=\"'+escape_comcode(result)+'\"]'+escape_comcode(vc)+'[/page]');}
function do_input_email(field_name,va)
{if(typeof window.insert_textbox=='undefined')return;window.fauxmodal_prompt('Enter the e-mail address',va,function(va)
{if((va!=null)&&(va.indexOf('@')==-1))
{window.fauxmodal_alert('You specified a non-valid e-mail address (e-mail addresses contain "@")',function(){do_input_url(field_name,va);});return;}
if(va!==null)
{window.fauxmodal_prompt('Enter the caption','',function(vb)
{var element=document.getElementById(field_name);element=ensure_true_id(element,field_name);if(vb!==null)insert_textbox(element,'[email=\"'+escape_comcode(vb)+'\"]'+escape_comcode(va)+'[/email]');},'Insert a Comcode e-mail address tag');}},'Insert a Comcode e-mail address tag');}
function do_input_b(field_name)
{if(typeof window.insert_textbox_wrapping=='undefined')return;var element=document.getElementById(field_name);element=ensure_true_id(element,field_name);insert_textbox_wrapping(element,'b','');}
function do_input_i(field_name)
{if(typeof window.insert_textbox_wrapping=='undefined')return;var element=document.getElementById(field_name);element=ensure_true_id(element,field_name);insert_textbox_wrapping(element,'i','');}
function do_input_font(field_name)
{if(typeof window.insert_textbox_wrapping=='undefined')return;var element=document.getElementById(field_name);element=ensure_true_id(element,field_name);var form=element.form;var face=form.elements['f_face'];var size=form.elements['f_size'];var colour=form.elements['f_colour'];if((face.value=='')&&(size.value=='')&&(colour.value==''))
{window.fauxmodal_alert('You must select at least one font option');return;}
insert_textbox_wrapping(document.getElementById(field_name),'[font=\"'+escape_comcode(face.value)+'\" color=\"'+escape_comcode(colour.value)+'\" size=\"'+escape_comcode(size.value)+'\"]','[/font]');}
function set_font_sizes(list)
{var i=0;for(i=1;i<list.options.length;i++)
{list.options[i].style.fontSize=list.options[i].value+'em';}}
function deset_font_sizes(list)
{var i=0;for(i=1;i<list.options.length;i++)
{list.options[i].style.fontSize='';}}
function init_form_saving(form_id)
{window.last_autosave=new Date();var form=document.getElementById(form_id);for(var i=0;i<form.elements.length;i++)
{if(field_supports_autosave(form.elements[i]))
{add_event_listener_abstract(form.elements[i],'keypress',handle_form_saving);add_event_listener_abstract(form.elements[i],'blur',handle_form_saving);form.elements[i].externalOnKeyPress=handle_form_saving;}}
add_event_listener_abstract(document.body,'keydown',function(form){return function(event){handle_form_saving_explicit(event,form);}}(form));var autosave_value=read_cookie(encodeURIComponent(get_autosave_url_stem()));if((autosave_value!='')&&(autosave_value!='0'))
{if(typeof window.localStorage!='undefined')
{var fields_to_do={},fields_to_do_counter=0,biggest_length_data='';var key,value;var element_name,autosave_name;for(var j=0;j<form.elements.length;j++)
{element_name=(typeof form.elements[j].name=='undefined')?form.elements[0][j].name:form.elements[j].name;autosave_name=get_autosave_name(element_name);if(typeof localStorage[autosave_name]!='undefined')
{key=autosave_name;value=localStorage[autosave_name];fields_to_do[element_name]=value;fields_to_do_counter++;if(value.length>biggest_length_data.length)
{biggest_length_data=value;}}else
{}}
if((fields_to_do_counter!=0)&&(biggest_length_data.length>25))
{_restore_form_autosave(form,fields_to_do,biggest_length_data);return;}else
{}}}else
{}
if(navigator.onLine)
{var url='https://www.lovinity.org/data/autosave.php?type=retrieve';url+='&stem='+window.encodeURIComponent(get_autosave_url_stem());url+=keep_stub();var callback=function(form){return function(result){_retrieve_form_autosave(result,form);}}(form);do_ajax_request(url,callback);}}
function _retrieve_form_autosave(result,form)
{var fields_to_do={},fields_to_do_counter=0,biggest_length_data='';var key,value;var fields=result.getElementsByTagName('field');var element,element_name,autosave_name;for(var i=0;i<fields.length;i++)
{key=fields[i].getAttribute('key');value=fields[i].getAttribute('value');element=null;for(var j=0;j<form.elements.length;j++)
{element_name=(typeof form.elements[j].name=='undefined')?form.elements[0][j].name:form.elements[j].name;autosave_name=get_autosave_name(element_name);if(autosave_name==key)
{element=form.elements[j];break;}}
if(element)
{fields_to_do[element_name]=value;fields_to_do_counter++;if(value.length>biggest_length_data.length)
{biggest_length_data=value;}}}
if((fields_to_do_counter!=0)&&(biggest_length_data.length>25))
{_restore_form_autosave(form,fields_to_do,biggest_length_data);}else
{}}
function _restore_form_autosave(form,fields_to_do,biggest_length_data)
{var autosave_name;biggest_length_data=biggest_length_data.replace(/<[^>]*>/g,'').replace(/\n/g,' ').replace(/&nbsp;/g,' ');if(biggest_length_data.length>100)biggest_length_data=biggest_length_data.substr(0,100)+'...';window.fauxmodal_confirm('You appear to have unsaved form data. Would you like to auto-fill this form with your prior data? If you say no, the data will be erased.\n\n'+biggest_length_data,function(result)
{if(result)
{for(key in fields_to_do)
{if(typeof fields_to_do[key]!='string')continue;if(typeof form.elements[key]!='undefined')
{if(typeof console.log!='undefined')console.log('Restoring '+key);clever_set_value(form,form.elements[key],fields_to_do[key]);}}}else
{set_cookie(encodeURIComponent(get_autosave_url_stem()),'0',0.167);if(typeof window.localStorage!='undefined')
{for(var key in fields_to_do)
{if(typeof fields_to_do[key]!='string')continue;autosave_name=get_autosave_name(key);if(typeof localStorage[autosave_name]!='undefined')
{delete localStorage[autosave_name];}}}}},'Auto-saving');}
function field_supports_autosave(element)
{if((typeof element.length!='undefined')&&(typeof element.nodeName=='undefined'))
{element=element[0];}
if(typeof element.name=='undefined')return false;var name=element.name;if(name=='')return false;if(name.substr(-2)=='[]')return false;if(is_wysiwyg_field(element))return true;if(element.disabled)return false;switch(element.nodeName.toLowerCase())
{case'textarea':case'select':return true;case'input':switch(element.type)
{case'checkbox':case'radio':case'text':case'color':case'date':case'datetime':case'datetime-local':case'email':case'month':case'number':case'range':case'tel':case'time':case'url':case'week':return true;}}
return false;}
function is_typed_input(element)
{if((typeof element.length!='undefined')&&(typeof element.nodeName=='undefined'))
{element=element[0];}
switch(element.nodeName.toLowerCase())
{case'textarea':return true;case'input':switch(element.type)
{case'hidden':case'text':case'color':case'date':case'datetime':case'datetime-local':case'email':case'month':case'number':case'range':case'tel':case'time':case'url':case'week':return true;}}
return false;}
function handle_form_saving_explicit(event,form)
{if(event.keyCode==83&&(navigator.platform.match('Mac')?event.metaKey:event.ctrlKey)&&(!navigator.platform.match('Mac')?event.ctrlKey:event.metaKey)&&(!event.altKey))
{event.preventDefault();var post='',found_validated_field=false,temp;for(var i=0;i<form.elements.length;i++)
{if(form.elements[i].name=='validated')found_validated_field=true;if(field_supports_autosave(form.elements[i]))
{temp=_handle_form_saving(event,form.elements[i],true);if(temp)
{if(post!='')post+='&';post+=window.encodeURIComponent(temp[0])+'='+window.encodeURIComponent(temp[1]);}}}
if(post!='')
{document.body.style.cursor='wait';if(navigator.onLine)
{post=modsecurity_workaround_ajax(post);do_ajax_request('/data/autosave.php?type=store'+keep_stub(),function(){if(document.body.style.cursor=='wait')document.body.style.cursor='';var message=found_validated_field?'Your draft has been saved. If you go away and come back to the form you will be asked if you would like to restore your typed data.\n\nThe process is not perfect so if possible you should do a proper form submission (unchecking Validated if you want to save as unpublished).\n\nNote that the draft does also auto-save with every 20 seconds of typing.':'Your draft has been saved. If you go away and come back to the form you will be asked if you would like to restore your typed data.\n\nThe process is not perfect so if possible you should do a proper form submission.\n\nNote that the draft does also auto-save with every 20 seconds of typing.';fauxmodal_alert(message,null,'Draft saving');},post);}}}}
function handle_form_saving(event,element,force)
{var temp=_handle_form_saving(event,element,force);if(temp)
{var post=window.encodeURIComponent(temp[0])+'='+window.encodeURIComponent(temp[1]);if(navigator.onLine)
{post=modsecurity_workaround_ajax(post);do_ajax_request('/data/autosave.php?type=store'+keep_stub(),function(){},post);}}}
function _handle_form_saving(event,element,force)
{if(typeof force=='undefined')force=(event.type=='blur');var this_date=new Date();if(!force)
{if((this_date.getTime()-window.last_autosave.getTime())<20*1000)return null;}
if(typeof event=='undefined')event=window.event;if(typeof element=='undefined')
{element=(typeof event.target!='undefined')?event.target:event.srcElement;}
if((typeof element=='undefined')||(element===null))
{return null;}
var value=clever_find_value(element.form,element);if((event.type=='keypress')&&(is_typed_input(element)))
{value+=String.fromCharCode(event.keyCode?event.keyCode:event.charCode);}
var element_name=(typeof element.name=='undefined')?element[0].name:element.name;var autosave_name=get_autosave_name(element_name);set_cookie(encodeURIComponent(get_autosave_url_stem()),'1',0.167);window.last_autosave=this_date;if(typeof window.localStorage!='undefined')
{try
{localStorage.setItem(autosave_name,value);}
catch(e){};}
return[autosave_name,value];}
function clever_set_value(form,element,value)
{if((typeof element.length!='undefined')&&(typeof element.nodeName=='undefined'))
{element=element[0];}
switch(element.nodeName.toLowerCase())
{case'textarea':set_textbox(element,value,value);break;case'select':for(var i=0;i<element.options.length;i++)
{if(element.options[i].value==value)
{element.selectedIndex=i;if(typeof $(element).select2!='undefined'){$(element).trigger('change');}}}
break;case'input':switch(element.type)
{case'checkbox':element.checked=(value!='');break;case'radio':value='';for(var i=0;i<form.elements.length;i++)
{if((form.elements[i].name==element.name)&&(form.elements[i].value==value))
form.elements[i].checked=true;}
break;case'text':case'color':case'date':case'datetime':case'datetime-local':case'email':case'month':case'number':case'range':case'search':case'tel':case'time':case'url':case'week':element.value=value;break;}}
if(element.onchange)element.onchange();}
function get_autosave_url_stem()
{var name='cms_autosave_'+window.location.pathname;if(window.location.search.indexOf('type=')!=-1)
{name+=window.location.search.replace(/[\?&]redirect=.*/,'').replace(/[\?&]keep_\w+=.*/,'').replace(/[\?&]cat=.*/,'');}
name=name.replace(/\./,'_');return name;}
function get_autosave_name(field_name)
{return get_autosave_url_stem()+':'+field_name;}