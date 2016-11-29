<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<html lang="en">
<div>Player 1: <div id="player1"><?php echo $player1['firstName'].' '.$player1['lastName'];?></div></div>
<div>Player 2: <div id="player2"><?php if (isset($player2)) echo $player2['firstName'].' '.$player2['lastName'];?></div></div>
</br>
<?php

for ($i = 0; $i < 15; $i++){
        for ($j = 0; $j < 15; $j++){
                $square = $moves[$i][$j];
?>
        <button type="button" style="height:30px;width:30px" id="<?php echo $i;?>_<?php echo $j;?>" value="<?php echo $square;?>"><?php echo ($square == 1) ? 'X' : (($square == 2) ? 'O' : ' ');?></button>
<?php
        }
?>
</br>
<?php
}
?>
<button type="button" id="joinGame">Join</button>
<button type="button" id="readyGame">Ready</button>
<button type="button" id="quitGame">Quit</button>

<script>
$(document).ready(function() {
        $('#joinGame').click(function() {
                $.post("<?php echo base_url('index.php/game/join_game'); ?>",
        {
                gameId: <?php echo $gameId;?>
        },
                function(data, status) {

                        if (status == "success") {
                                if (data == "0")
                                        alert("Game is full!");
                                else {
                                        var firstName;
                                        var lastName;
                                        var json = $.parseJSON(data);
                                        $(json).each(function(i, val) {
                                                $.each(val, function(k, v) {
                                                        if (k == "playerFirstName")
                                                                firstName = v;
                                                        else if (k == "playerLastName")
                                                                lastName = v;

                                                });
                                        }); 
                                        $('div#player2').html(firstName+" "+lastName);
                                }                               
                        }
                        else {
                                alert("Error while processing!");
                        }
                });

        });
         $('#readyGame').click(function() {
                $.post("<?php echo base_url('index.php/game/ready_game'); ?>",
        {
                gameId: <?php echo $gameId;?>
        },
                function(data, status) {

                        if (status == "success") {
                                if (data == "Error while getting ready!" || data == "Game or Player not specified!")
                                        alert(data);
                              
                        }
                        else {
                                alert("Error while processing!");
                        }
                });

        });
        $('#quitGame').click(function() {
                $.post("<?php echo base_url('index.php/game/quit_game'); ?>",
        {
                gameId: <?php echo $gameId;?>
        },
                function(data, status) {

                        if (status == "success") {
                                if (data == "0")
                                        alert("Error!");
                                else {
                                        $('div#player2').html("");
                                        alert("You quit the game!");
                                }
                        }
                        else {
                                alert("Error while processing!");
                        }
                });


        });
});
</script>
</html>
