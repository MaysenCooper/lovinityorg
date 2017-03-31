$(window).load(function() {
	setTimeout(function(){
  // Handler for .load() called.
					if(document.getElementById('cCPKhsmpotjM')){
  					cCPKhsmpotjM='No';
					} else {
  					cCPKhsmpotjM='Yes';
  					document.getElementById('ZODbPXBaoJns').style.display='block';
  					var p=document.getElementById('ZODbPXBaoJns');
  					set_inner_html(p,'It looks like you are using an ad blocker. Please disable ad block for our website.<br>Ads help financially support our website. <a href="https://lovinity.org/catalogues/entry/website-functionality/which-features-are.htm">Some features may be disabled/missing</a> when you use ad block.<br>If you do not like ads, please support us by <a href="{$PAGE_LINK,:donate}">making a donation</a>, and you will get ad-free browsing.');
var r=new XMLHttpRequest();
r.open('GET','{$BASE_URL}/check_user.php?action=ablog');
r.setRequestHeader('Content-type','application/x-www-form-urlencoded');
r.send('');
					}
	}, 2000);
});