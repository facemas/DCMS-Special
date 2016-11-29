<?php
if (!cache_events::get('log_archive')) {
    cache_events::set('log_archive', true, mt_rand(82800, 86400));
    $log_files = (array) @glob(H.'/sys/logs/*.log');
    foreach ($log_files AS $path) {
        if (filesize($path) < 1048576) continue;
        $filename = basename($path, '.log');
        $zip_file = H.'/sys/logs/'.$filename.'_'.date("Y.m.d_H.i").'.zip';
        $zip = new PclZip($zip_file);
        $zip->create($path, PCLZIP_OPT_REMOVE_ALL_PATH);
        @unlink($path);
    }
}
