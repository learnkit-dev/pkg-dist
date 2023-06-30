<?php

namespace App\Actions;

use Sentry;
use Sentry\Breadcrumb;

class DownloadDistFromSource
{
    public static function download($url, $path)
    {
        $zip = fopen($path, 'w');

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_TIMEOUT, 100);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 100);
        curl_setopt($curl, CURLOPT_AUTOREFERER, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_BINARYTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_FILE, $zip);
        curl_setopt($curl, CURLOPT_USERAGENT, config('app.name') . ' / ' . env('APP_DOMAIN'));
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Authorization: token ' . config('repomap.github_personal_token')));
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        $httpcode = curl_getinfo($curl , CURLINFO_HTTP_CODE);
        $result = curl_exec($curl);
        curl_close($curl);

        Sentry::addBreadcrumb(
            new Breadcrumb(
                Breadcrumb::LEVEL_INFO,
                Breadcrumb::TYPE_DEFAULT,
                'dist',
                'Download dist from source',
                [
                    'result' => $result,
                    'status' => $httpcode,
                ]
            )
        );

        return $httpcode;
    }
}
