<?php

namespace gingerberry\api\v1\handler;

use gingerberry\router\Router;
use gingerberry\api\v1\handler\Handler;
use gingerberry\db\DB;
use gingerberry\Config;

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
    }

    private function uploadVideoEndpoint()
    {
        $this->router->post("/\/ginger\/api\/v1\/video\/[0-9]+/", function ($request) {
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
                    'ACL'        => 'public-read'
                ]);
            } catch (S3Exception $e) {
                header("{$this->router->getRequest()->serverProtocol} 500 Internal Server Error");
                return $e->getMessage() . PHP_EOL;
            }

            $this->updateSlideStamps($_FILES["video"]["tmp_name"], $id);

            return \json_encode("");
        });
    }

    private function updateSlideStamps($filePath, $presentationID)
    {
        $videoStamps = $this->videoStamping($filePath, $presentationID);

        $dbConn = DB::getInstance()::getPDO();
        $sql = "SELECT id FROM slides WHERE presentation_id = :presentation_id";
        $stmt = $dbConn->prepare($sql, array(\PDO::ATTR_CURSOR => \PDO::CURSOR_FWDONLY));
        $stmt->execute(array(':presentation_id' => $presentationID));

        $cnt = 0;

        while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
            $slideID = $row['id'];
            if (!\array_key_exists($cnt, $videoStamps)) {
                break;
            }

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

    private function videoStamping($filePath, $presentationID)
    {
        $frameRate = shell_exec("(" . Config::FFPROBE . " -v error -select_streams v -of default=noprint_wrappers=1:nokey=1 -show_entries stream=r_frame_rate $filePath 2> /dev/null | cut -d '/' -f 1)");
        $frameRate = intval($frameRate);

        $videoLen = shell_exec(Config::FFPROBE . " -v error -show_entries format=duration -of default=noprint_wrappers=1:nokey=1 $filePath 2> /dev/null");
        $videoLen = intval($videoLen);
        $frameFilePath = $this->getTmpFramesDir($presentationID);

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

            shell_exec(Config::FFMPEG . " -i $filePath -vf 'select=eq(n\, $frame)' -y -vframes 1 $file");

            $qrcode = new QrReader($file);
            $curr = intval($qrcode->text());

            if ($curr != $prev) {
                for ($i = $prev + 1; $i <= $curr; $i++) {
                    $tsArray[$i] = $time - $step;
                }
                $prev = $curr;
            }

            shell_exec("rm $file");
        }

        rmdir($frameFilePath);

        return $tsArray;
    }

    private function getTmpFramesDir($presentationID)
    {
        $frameFilePath = sys_get_temp_dir() . "/tmp_frames/" . $presentationID . "/";

        if (!\file_exists($frameFilePath)) {
            \mkdir($frameFilePath, 0755, true);
        }

        return $frameFilePath;
    }
}
