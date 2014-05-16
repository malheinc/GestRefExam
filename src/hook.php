<?php

function _empty($value)
{
        return empty($value);
}

function _isset($value)
{
        return isset($value);
}

function _reset($value)
{
        return reset($value);
}

function _end($value)
{
        return end($value);
}

function toUTF8($string = '')
{
        return mb_convert_encoding($string, 'UTF-8', mb_detect_encoding($string, array('UTF-8', 'ISO-8859-1', 'ASCII'), true));
}