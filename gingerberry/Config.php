<?php

namespace gingerberry;

class Config
{
    const FFPROBE = "/usr/local/bin/ffprobe";
    const FFMPEG = "/usr/local/bin/ffmpeg";
    const DB_HOST = "localhost";
    const DB_PORT = "3306";
    const DB_NAME = "gingerberry";
    CONST DB_USR = "root";
    CONST DB_PWD = "";

    private function __construct()
    {
    }
}
