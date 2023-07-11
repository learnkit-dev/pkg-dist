<?php

if (! function_exists('formatFilesize')) {
    function formatFilesize($size) {
        $kb = $size / 1000;

        if ($kb < 1000) {
            return $kb . ' KB';
        }

        $mb = $kb / 1000;

        if ($mb < 1000) {
            return $mb . ' MB';
        }

        $gb = $mb / 1000;

        return $gb . ' GB';
    }
}
