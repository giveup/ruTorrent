<?php
declare(strict_types=1);

const DEFAULT_LANGUAGE = 'en';

function GetActiveLanguage()
{
    list($languages, $confidence) = explode(';', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
    $languages = array_filter(explode(',', $languages), function ($lang) {
        return preg_match('[a-z]{2}(\-[A-Z]{2})?', $lang);
    });

    return 'en';
}

echo '<script id="locale-dict" type="text/javascript" src="./lang/'. GetActiveLanguage().'.js"></script>';
