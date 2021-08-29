<?php
require_once("./GameLoop.php");
require_once("./GameState.php");
$screenWidth = 800;
$screenHeight = 450;
$gameLoop = new GameLoop($screenWidth, $screenHeight);
$gameLoop->start();
