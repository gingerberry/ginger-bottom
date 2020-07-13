<?php

namespace gingerberry;

class Config
{
    const FFPROBE = "/usr/local/bin/ffprobe";
    const FFMPEG = "/usr/local/bin/ffmpeg";
    
    const DB_HOST = "localhost";
    const DB_PORT = "3306";
    const DB_NAME = "gingerberry";
    const DB_USR = "root";
    const DB_PWD = "";

    const LOCAL_STORAGE = "/Applications/XAMPP/xamppfiles/htdocs/ginger_storage/";
    const S3_BUCKET_NAME = "";
    const S3_REGION = "us-east-1";

    private function __construct()
    {
    }
}
