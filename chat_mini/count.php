<?php

$post->counter = DB::me()->query(" SELECT COUNT( * ) FROM `chat_mini` ")->fetchColumn();
