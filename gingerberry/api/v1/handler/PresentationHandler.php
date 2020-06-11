<?php

namespace gingerberry\api\v1\handler;

use gingerberry\router\Router;
use gingerberry\api\v1\handler\Handler;
use gingerberry\api\v1\model\Presentation;
use gingerberry\api\v1\model\DetailedPresentation;
use gingerberry\api\v1\model\PresentationList;
use gingerberry\api\v1\model\Slide;
use gingerberry\db\DB;

use Aws\S3\S3Client;
use Aws\S3\Exception\S3Exception;

class PresentationHandler extends Handler
{
    private $router;

    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    public function discoverEndpoints()
    {
        $this->registerRecentPresentationsEndpoint();
        $this->registerPresentationEndpoint();
        $this->getImageEndpoint();
    }

    private function registerRecentPresentationsEndpoint()
    {
        $this->router->get("/\/ginger\/api\/v1\/recentPresentations/", function () {
            $this->setCORSHeaders();
            header("Content-Type: application/json; charset=UTF-8");

            $dbConn = DB::getInstance()::getPDO();
            $sql = "SELECT * FROM presentations ORDER BY id DESC LIMIT 0, 10;";
            $stmt = $dbConn->prepare($sql);
            $stmt->execute();

            $pptArr = array();

            while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                $id = $row['id'];
                $name = $row['presentation_name'];

                \array_push($pptArr, new Presentation($id, $name));
            }

            $stmt = null;

            $presentationList = new PresentationList($pptArr);
            return \json_encode($presentationList);
        });
    }

    private function registerPresentationEndpoint()
    {
        $this->router->get('/\/ginger\/api\/v1\/presentation\/[0-9]+/', function () {
            $this->setCORSHeaders();
            header("Content-Type: application/json; charset=UTF-8");

            $id = basename($_SERVER['REQUEST_URI']);

            $dbConn = DB::getInstance()::getPDO();
            $sql = "SELECT * FROM presentations WHERE id = :id";
            $stmt = $dbConn->prepare($sql, array(\PDO::ATTR_CURSOR => \PDO::CURSOR_FWDONLY));
            $stmt->execute(array(':id' => $id));

            $result = $stmt->fetchAll(\PDO::FETCH_ASSOC)[0];
            $ppt = new DetailedPresentation($result['id'], $result['presentation_name'], []);
            $stmt = null;

            $sql = "SELECT * FROM slides WHERE presentation_id = :presentation_id ORDER BY id ASC";
            $stmt = $dbConn->prepare($sql, array(\PDO::ATTR_CURSOR => \PDO::CURSOR_FWDONLY));
            $stmt->execute(array(':presentation_id' => $id));

            while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                $slideID = $row['id'];
                $slideTitle = $row['title'];
                $startSec = $row['start_sec'];

                \array_push($ppt->slides, new Slide($slideID, $id, $slideTitle, $startSec));
            }

            $stmt = null;

            return \json_encode($ppt);
        });
    }

    public function getImageEndpoint()
    {
        $this->router->get("/\/ginger\/api\/v1\/presentation_image\/[0-9]+\/slide\/[0-9]+/", function () {
            $this->setCORSHeaders();
            $id = explode('/', $_SERVER['REQUEST_URI'])[5];
            $image = explode('/', $_SERVER['REQUEST_URI'])[7];
            
            $s3 = new S3Client([
                'version' => 'latest',
                'region'  => 'us-east-1'
            ]);

            $keyname = "presentation/$id/$image.png"; 

            $result = $s3->getObject([
                'Bucket' => 'gingerberry',
                'Key'    => $keyname
            ]);

            header("Content-Type: {$result['ContentType']}");
            header("Content-Disposition: attachment; filename=$id.png");

            return $result['Body'];
        });
    }
}
