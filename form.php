<!-- 
form.php
The form for requesting a trade.
You can configure this however you want, just don't change the 'action' of the form or the 'name' of the form fields.
-->
<form method="post" action="trade.php">
	<p class="name">
		<input type="text" name="name" id="name" value="Name" />
	</p>
	
	<p class="email">
		<input type="text" name="email" id="email" value="Email" />
	</p>
	
	<p class="web">
		<input type="text" name="url" id="url" value="http://yourwebsite.com" />
	</p>
	
	<p class="patch">
		<input type="text" name="patch" id="patch" value="Patch URL" />
	</p>
	
	<p class="member">
		<input type="text" name="member_num" id="member_num" class="auto" size="3" maxlength="3" value="Member #" />
	</p>
	
	<p class="about">
		<textarea name="comments" id="comments" rows="5" cols="20">Tell me about yourself.</textarea>
	</p>
	
	<p class="submit">
		<input type="submit" value="Trade With Me!" class="button" />
	</p>
</form>