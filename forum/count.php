<?php

$post->counter = DB::me()->query(" SELECT COUNT( * ) FROM `forum_themes` ")->fetchColumn();
?>