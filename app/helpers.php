<?php

if (! function_exists('formatFilesize')) {
    function formatFilesize($size) {
        $kb = $size / 1000;

        if ($kb < 1000) {
            return $kb . ' kb';
        }

        $mb = $kb / 1000;

        if ($mb < 1000) {
            return $mb . ' mb';
        }

        $gb = $mb / 1000;

        return $gb . ' gb';
    }
}
