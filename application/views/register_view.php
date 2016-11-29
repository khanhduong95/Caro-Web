<!DOCTYPE html>
<script type="text/javascript" src="<?php echo base_url();?>assets/js/jquery-3.1.1.min.js"></script>
<script src="<?php echo base_url();?>assets/js/jquery-ui-1.12.1/jquery-ui.js"></script>
<script src="<?php echo base_url();?>assets/js/jquery-ui-1.12.1/jquery-ui.min.js"></script>
<?php
if (!empty($success)){
?>
<div><?php echo $success; ?></div>
<?php
}
else {
        echo form_open('player/register', array('class' => 'form-register')); ?>
<label>Email</label>
<div>
        <input id="email" name="email" type="email" required/>
</div></br>
<label>First Name</label>
<div>
        <input id="firstName" name="firstName" type="text" required/>
</div></br>
<label>Last Name</label>
<div>
        <input id="lastName" name="lastName" type="text" required/>
</div></br>
<label>Date of Birth</label>
<div>
        <input id="dob" name="dob" type="date" required/>
</div></br>
<label>Password</label>
<div>
        <input id="password" name="password" type="password" required/>
</div></br>
<label>Confirm Password</label>
<div>
        <input id="confirmPassword" name="confirmPassword" type="password" required/>
</div></br>
<div><?php if (isset($error['email'])) echo $error['email'];?></div></br>
<div><?php if (isset($error['password'])) echo $error['password'];?></div></br>
<div><?php if (isset($error['register'])) echo $error['register'];?></div></br>

<button type="submit" id="submit" name="submit" value="Submit">Submit</button>

<?php echo form_close();
}
?>
<script>
var datefield=document.createElement("input");
datefield.setAttribute("type", "date");
if (datefield.type!="date"){ //if browser doesn't support input type="date", initialize date picker widget:
        $(document).ready(function(){ //on document.ready
                $('#dob').datepicker();
        });
}
</script>
