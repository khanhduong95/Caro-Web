<?php echo form_open('player/login', array('class' => 'form-login')); ?>
<label>Email</label>
<div>
<input id="email" name="email" type="email"/>
</div></br>
<label>Password</label>
<div>
<input id="password" name="password" type="password"/>
</div></br>
<button id="submit" type="submit" name="submit" value="Submit">Submit</button>

<?php echo form_close(); ?>
