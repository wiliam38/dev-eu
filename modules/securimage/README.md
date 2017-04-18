PUT IMAGE:
<img id="captcha" src="{$base_url}securimage/show" alt="CAPTCHA Image"/>
	
PUT INPUT FIELD:
<input type="text" name="captcha_code" size="10" maxlength="6" />
<a href="#" onclick="$('#captcha').attr('src', '{$base_url}securimage/show?' + Math.random()); return false">[ Different Image ]</a>

CHECK CODE
if (!Captcha::check($captcha_code)) { echo "Security code error!"; exit(); }

CLEAR GENERATED CODE
Captcha::clear();
