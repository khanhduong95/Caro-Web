<!DOCTYPE html>
<script type="text/javascript" src="<?php echo base_url();?>assets/js/jquery-3.1.1.min.js"></script>
<link href="<?php echo base_url();?>assets/js/jquery-ui-1.12.1/jquery-ui.css" rel="stylesheet" type="text/css" />
<script src="<?php echo base_url();?>assets/js/jquery-ui-1.12.1/jquery-ui.js"></script>
<script src="<?php echo base_url();?>assets/js/jquery-ui-1.12.1/jquery-ui.min.js"></script>
<?php echo form_open('player/register', array('class' => 'form-register')); ?>
<label>Email</label>
<div>
<input id="email" name="email" type="email"/>
</div></br>
<label>First Name</label>
<div>
<input id="firstName" name="firstName" type="text"/>
</div></br>
<label>Last Name</label>
<div>
<input id="lastName" name="lastName" type="text"/>
</div></br>
<label>Date of Birth</label>
<div>
<input id="dob" name="dob" type="date"/>
</div></br>
<label>Password</label>
<div>
<input id="password" name="password" type="password"/>
</div></br>
<button id="submit" type="submit" name="submit">Submit</button>

<?php echo form_close(); ?>
<script>
var datefield=document.createElement("input");
datefield.setAttribute("type", "date");
if (datefield.type!="date"){
        $(document).ready(function(){
                $('#dob').datepicker();
        });
}
</script>
