<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<html lang="en">
<div>Player 1: <p id="player1FirstName"></p><p>&nbsp;</p><p><p id="player1LastName"></p></div>
<div>Player 2: <p id="player2FirstName"></p><p>&nbsp;</p><p><p id="player2LastName"></p></div>
</br>
<div id="caro-table"></div>
<button type="button" id="joinGame">Join</button>
<button type="button" id="readyGame">Ready</button>
<button type="button" id="quitGame">Quit</button>

<script>
$(document).ready(function() {

        var player1Id = 0;
        var player2Id = 0;

        for (i = 0; i < 15; i++){
                $("#caro-table").append("<tr id='row_"+i+"'></tr>");
                for (j = 0; j < 15; j++){
                        $("#row_"+i).append("<td id='cell_"+j+"'><button class='caro-square' id='square_"+i+"_"+j+"' style='width: 30px; height: 30px;'></button></td>");
                }
        }

        function refreshGame(){

                var player1FirstName = $('#player1FirstName').html();
                var player2FirstName = $('#player2FirstName').html();
                var player1LastName = $('#player1LastName').html();
                var player2LastName = $('#player2LastName').html();

                var moves = [];
                for (i = 0; i < 15; i++){
                        moves[i] = [];
                        for (j = 0; j < 15; j++){
                                moves[i][j] = ($('#square_'+i+'_'+j).html() == 'X') ? 1:(($('#square_'+i+'_'+j).html() == 'O') ? 2:0);
                        }
                }

                $.post("<?php echo base_url('index.php/game/refresh_game'); ?>",
                {
                        gameId: <?php echo $gameId;?>,
                                player1Id: player1Id,
                                player2Id: player2Id,
                                moves: moves

                },
                        function(data, status) {

                                if (status == "success") {
                                        if (data == "0")
                                                alert("Game is closed!");
                                        else {

                                                var json = $.parseJSON(data);
                                                $(json).each(function(i, val) {
                                                        $.each(val, function(k, v) {
                                                                if (k == "player1Id")
                                                                        player1Id = v;
                                                                else if (k == "player1FirstName")
                                                                        $('#player1FirstName').html(v); 
                                                                else if (k == "player1LastName")
                                                                        $('#player1LastName').html(v);
                                                                else if (k == "player2Id")
                                                                        player2Id = v;
                                                                else if (k == "player2FirstName")
                                                                        $('#player2FirstName').html(v); 
                                                                else if (k == "player2LastName")
                                                                        $('#player2LastName').html(v);
                                                                else if (k == "moves"){
                                                                        for (i = 0; i < 15; i++){
                                                                                for (j = 0; j < 15; j++){
                                                                                        $('#square_'+i+'_'+j).html((v[i][j] == 1) ? 'X':((v[i][j] == 2) ? 'O':''));
                                                                                }
                                                                        }
                                                                }


                                                        });
                                                }); 

                                                refreshGame();
                                        }                               
                                }
                                else {
                                        alert("Error while processing!");
                                }
                        });

        }

        $('#joinGame').click(function() {
                $.post("<?php echo base_url('index.php/game/join_game'); ?>",
        {
                gameId: <?php echo $gameId;?>
        },
                function(data, status) {

                        if (status == "success") {
                                if (data == "0")
                                        alert("Game is full!");

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

                                        alert("You quit the game!");
                                }
                        }
                        else {
                                alert("Error while processing!");
                        }
                });

        });

        $('.caro-square').click(function() {
                var squareId = $(this).attr('id').split('square_')[1].split('_');
                var moveX = squareId[0];
                var moveY = squareId[1];
                $.post("<?php echo base_url('index.php/game/update_move'); ?>",
                {
                        gameId: <?php echo $gameId;?>,
                                moveX: moveX,
                                moveY: moveY
                },
                        function(data, status) {

                                if (status == "success") {
                                        if (data == "Error while making a move!" || data == "You don't have permission to make a move!" || data == "Game is closed!")
                                                alert(data);

                                }
                                else {
                                        alert("Error while processing!");
                                }
                        });               
        });

        refreshGame();

});
</script>
</html>
