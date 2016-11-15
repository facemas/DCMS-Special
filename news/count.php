<?php

$post->counter = DB::me()->query(" SELECT COUNT(*) FROM `news` ")->fetchColumn();
