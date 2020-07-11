<?php

namespace gingerberry;

class Config
{
    const FFPROBE = "/usr/local/bin/ffprobe";
    const FFMPEG = "/usr/local/bin/ffmpeg";
    const DB_HOST = "gingerberry.cwch0ro4xne5.us-east-1.rds.amazonaws.com";
    const DB_PORT = "3306";
    const DB_NAME = "gingerberry";
    CONST DB_USR = "admin";
    CONST DB_PWD = "gingerberry";

    private function __construct()
    {
    }
}
