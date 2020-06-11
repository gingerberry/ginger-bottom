<?php

namespace gingerberry\api\v1\handler;

use gingerberry\router\Router;
use gingerberry\api\v1\handler\Handler;
use gingerberry\db\DB;

use Aws\S3\S3Client;
use Aws\S3\Exception\S3Exception;

class VideoHandler extends Handler
{
    private $router;

    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    public function discoverEndpoints()
    {
        $this->uploadVideoEndpoint();
    }

    private function uploadVideoEndpoint()
    {
        $this->router->post("/\/ginger\/api\/v1\/video\/[0-9]+/", function ($request) {
            $this->setCORSHeaders();
            header("Content-Type: application/json; charset=UTF-8");

            $id = basename($_SERVER['REQUEST_URI']);
            $target_dir = "presentation/$id/";
            $fileType = strtolower(pathinfo($_FILES["video"]["name"], PATHINFO_EXTENSION));
            $target_file = $target_dir . $id . ".$fileType";
            if ($fileType != "mp4") {
                return \json_encode("Invalid file format. Expected .mp4 but received $fileType.");
            }

            //move_uploaded_file($_FILES["video"]["tmp_name"], $target_file);

            $s3 = new S3Client([
                'version' => 'latest',
                'region'  => 'us-east-1'
            ]);

            try {
                // Upload data.
                $s3->putObject([
                    'Bucket' => "gingerberry",
                    'Key'    => $target_file,
                    'Body'   => file_get_contents($_FILES["video"]["tmp_name"]),
                ]);
            } catch (S3Exception $e) {
                header("{$this->router->getRequest()->serverProtocol} 500 Internal Server Error");
                return $e->getMessage() . PHP_EOL;
            }

            return \json_encode("");
        });
    }
}
