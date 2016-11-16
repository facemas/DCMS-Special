<?php

$post->counter = DB::me()->query(" SELECT COUNT( * ) FROM `blog` ")->fetchColumn();
?>