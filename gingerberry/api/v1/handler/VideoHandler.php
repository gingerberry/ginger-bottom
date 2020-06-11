<?php

namespace gingerberry\api\v1\handler;

use gingerberry\router\Router;
use gingerberry\api\v1\handler\Handler;
use gingerberry\db\DB;

use Aws\S3\S3Client;
use Aws\S3\Exception\S3Exception;

use Zxing\QrReader;

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
        $this->getVideoEndpoint();
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

            $s3 = new S3Client([
                'version' => 'latest',
                'region'  => 'us-east-1'
            ]);

            try {
                $s3->putObject([
                    'Bucket' => "gingerberry",
                    'Key'    => $target_file,
                    'Body'   => file_get_contents($_FILES["video"]["tmp_name"]),
                ]);
            } catch (S3Exception $e) {
                header("{$this->router->getRequest()->serverProtocol} 500 Internal Server Error");
                return $e->getMessage() . PHP_EOL;
            }

            $this->updateSlideStamps($_FILES["video"]["tmp_name"], $id);

            return \json_encode("");
        });
    }

    public function getVideoEndpoint()
    {
        $this->router->get("/\/ginger\/api\/v1\/video\/[0-9]+/", function ($request) {
            $this->setCORSHeaders();
            $id = basename($_SERVER['REQUEST_URI']);
            
            $s3 = new S3Client([
                'version' => 'latest',
                'region'  => 'us-east-1'
            ]);

            $keyname = "presentation/$id/$id.mp4"; 

            $result = $s3->getObject([
                'Bucket' => 'gingerberry',
                'Key'    => $keyname
            ]);

            header("Content-Type: {$result['ContentType']}");
            header("Content-Disposition: attachment; filename=$id.mp4");

            return $result['Body'];
        });
    }

    private function updateSlideStamps($filePath, $presentationID)
    {
        $videoStamps = $this->videoStamping($filePath);

        $dbConn = DB::getInstance()::getPDO();
        $sql = "SELECT id FROM slides WHERE presentation_id = :presentation_id";
        $stmt = $dbConn->prepare($sql, array(\PDO::ATTR_CURSOR => \PDO::CURSOR_FWDONLY));
        $stmt->execute(array(':presentation_id' => $presentationID));

        $cnt = 0;

        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $slideID = $row['id'];

            $sql = "UPDATE slides SET start_sec = :start_sec WHERE presentation_id = :presentation_id AND id = :id";
            $slideSTMT = $dbConn->prepare($sql, array(\PDO::ATTR_CURSOR => \PDO::CURSOR_FWDONLY));
            $slideSTMT->execute(array(
                ':start_sec' => $videoStamps[$cnt],
                ':presentation_id' => $presentationID,
                ':id' => $slideID
            ));
            $slideSTMT = null;

            $cnt++;
        }

        $stmt = null;
    }

    private function videoStamping($filePath)
    {
        set_time_limit(500);
        $frameRate = shell_exec("(ffprobe -v error -select_streams v -of default=noprint_wrappers=1:nokey=1 -show_entries stream=r_frame_rate $filePath 2> /dev/null | cut -d '/' -f 1)");
        $frameRate = intval($frameRate);

        $videoLen = shell_exec("ffprobe -v error -show_entries format=duration -of default=noprint_wrappers=1:nokey=1 $filePath 2> /dev/null");
        $videoLen = intval($videoLen);
        $frameFilePath = "tmp_frames/";

        $tsArray = array();

        $time = 0;
        $step = 5;

        $tsArray[0] = 0;
        $prev = 0;
        $curr = $prev;

        while ($time + $step < $videoLen) {
            $time += $step;
            $frame = $frameRate * $time;
            $file = $frameFilePath . ($time / $step) . ".png";
            shell_exec("ffmpeg -i $filePath -vf 'select=eq(n\, $frame)' -vframes 1 $file <<< y");

            $qrcode = new QrReader($file);
            $curr = intval($qrcode->text());

            if ($curr != $prev) {
                for ($i = $prev + 1; $i <= $curr; $i++) {
                    $tsArray[$i] = $time - $step;
                }
                $prev = $curr;
            }
        }

        return $tsArray;
    }
}
