"use strict";

function decrypt_data(encrypted_data)
{
	if (document.getElementById('decryption_overlay')) return;

	var container=document.createElement('div');
	container.className='decryption_overlay box';
	container.id='decryption_overlay';
	container.style.position='absolute';
	container.style.width='26em';
	container.style.padding='0.5em';
	container.style.left=(get_window_width()/2-200).toString()+'px';
	container.style.top=(get_window_height()/2-100).toString()+'px';
	try
	{
		window.scrollTo(0,0);
	}
	catch (e) {}

	var title=document.createElement('h2');
	title.appendChild(document.createTextNode('{!encryption:DECRYPT_TITLE;^}'));
	container.appendChild(title);

	var description=document.createElement('p');
	description.appendChild(document.createTextNode('{!encryption:DECRYPT_DESCRIPTION;^}'));
	container.appendChild(description);

	var form=document.createElement('form');
	form.action='';
	form.method='post';
	container.appendChild(form);

	var label=document.createElement('label');
	label.setAttribute('for','decrypt');
	label.appendChild(document.createTextNode('{!encryption:DECRYPT_LABEL;^}'));
	form.appendChild(label);

	var space=document.createTextNode(' ');
	form.appendChild(space);

	var token=document.createElement('input');
	token.type='hidden';
	token.name='csrf_token';
	token.id='csrf_token';
	token.value=get_csrf_token();
	form.appendChild(token);

	var input=document.createElement('input');
	input.type='password';
	input.name='decrypt';
	input.id='decrypt';
	form.appendChild(input);

	var proceed_div=document.createElement('div');
	proceed_div.className='proceed_button';
	proceed_div.style.marginTop='1em';

	// Cancel button
	var button=document.createElement('input');
	button.type='button';
	button.className='buttons__cancel button_screen_item';
	button.value='{!INPUTSYSTEM_CANCEL;^}';
	// Remove the form when it's cancelled
	add_event_listener_abstract(button,'click',function() { document.getElementsByTagName('body')[0].removeChild(container); return false; });
	proceed_div.appendChild(button);

	// Submit button
	button=document.createElement('input');
	button.type='submit';
	button.className='buttons__proceed button_screen_item';
	button.value='{!encryption:DECRYPT;^}';
	// Hide the form upon submission
	add_event_listener_abstract(button,'click',function() { container.style.display='none'; return true; });
	proceed_div.appendChild(button);

	form.appendChild(proceed_div);

	document.getElementsByTagName('body')[0].appendChild(container);

	window.setTimeout(function() {
		try
		{
			input.focus();
		}
		catch (e) {}
	},0);
}

function decrypt_it(encrypted_id, passcode)
{
	if (passcode == null)
	{
	var needs_passcode=load_snippet('decrypt','action=needspasscode&data=' + encodeURIComponent(get_inner_html(document.getElementById('decrypt_' + encrypted_id))));
	if (needs_passcode == 1)
	{
		var container=document.createElement('div');
		container.className='decryption_overlay box';
		container.id='decryption_overlay';
		container.style.position='absolute';
		container.style.width='26em';
		container.style.padding='0.5em';
		container.style.left=(get_window_width()/2-200).toString()+'px';
		container.style.top=(get_window_height()/2-100).toString()+'px';
		try
		{
			window.scrollTo(0,0);
		}
		catch (e) {}

		var title=document.createElement('h2');
		title.appendChild(document.createTextNode('{!encryption:DECRYPT_TITLE;^}'));
		container.appendChild(title);

		var description=document.createElement('p');
		description.appendChild(document.createTextNode('{!encryption:DECRYPT_DESCRIPTION;^}'));
		container.appendChild(description);

		var form=document.createElement('form');
		form.action='';
		form.method='post';
		container.appendChild(form);

		var label=document.createElement('label');
		label.setAttribute('for','decrypt');
		label.appendChild(document.createTextNode('{!encryption:DECRYPT_LABEL;^}'));
		form.appendChild(label);

		var space=document.createTextNode(' ');
		form.appendChild(space);

		var token=document.createElement('input');
		token.type='hidden';
		token.name='csrf_token';
		token.id='csrf_token';
		token.value=get_csrf_token();
		form.appendChild(token);
		
		var encryptedid=document.createElement('input');
		encryptedid.type='hidden';
		encryptedid.name='encrypted_id';
		encryptedid.id='encrypted_id';
		encryptedid.value=encrypted_id;
		form.appendChild(encryptedid);

		var input=document.createElement('input');
		input.type='password';
		input.name='decrypt';
		input.id='decrypt';
		form.appendChild(input);

		var proceed_div=document.createElement('div');
		proceed_div.className='proceed_button';
		proceed_div.style.marginTop='1em';

		// Cancel button
		var button=document.createElement('input');
		button.type='button';
		button.className='buttons__cancel button_screen_item';
		button.value='{!INPUTSYSTEM_CANCEL;^}';
		// Remove the form when it's cancelled
		add_event_listener_abstract(button,'click',function() { document.getElementsByTagName('body')[0].removeChild(container); return false; });
		proceed_div.appendChild(button);

		// Submit button
		button=document.createElement('input');
		button.type='button';
		button.className='buttons__proceed button_screen_item';
		button.value='{!encryption:DECRYPT;^}';
		// Hide the form upon submission
		add_event_listener_abstract(button,'click',function() { container.style.display='none'; decrypt_it2(form); document.getElementsByTagName('body')[0].removeChild(container); return false; });
		proceed_div.appendChild(button);

		form.appendChild(proceed_div);

		document.getElementsByTagName('body')[0].appendChild(container);

		window.setTimeout(function() {
			try
			{
				input.focus();
			}
			catch (e) {}
		},0);
		console.log("Form built"); 
	} else if (needs_passcode == 0)
	{
		set_inner_html(document.getElementById('decrypt_' + encrypted_id),load_snippet('decrypt','action=decrypt&data=' + encodeURIComponent(get_inner_html(document.getElementById('decrypt_' + encrypted_id)))));
		set_inner_html(document.getElementById('decryptbutton_' + encrypted_id),'');
	}
	} else {
		set_inner_html(document.getElementById('decrypt_' + encrypted_id),load_snippet('decrypt','action=decrypt&passcode=' + encodeURIComponent(passcode) + '&data=' + encodeURIComponent(get_inner_html(document.getElementById('decrypt_' + encrypted_id)))));
		set_inner_html(document.getElementById('decryptbutton_' + encrypted_id),'');
	}
	return false;
}

function decrypt_it2(form)
{
	decrypt_it(form.encrypted_id.value, form.decrypt.value);
	return false;
}