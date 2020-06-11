<?php

namespace gingerberry\api\v1\handler;

use gingerberry\router\Router;
use gingerberry\api\v1\handler\IHandler;
use gingerberry\api\v1\model\Presentation;
use gingerberry\api\v1\model\DetailedPresentation;
use gingerberry\api\v1\model\PresentationList;

class PresentationHandler implements IHandler
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
    }

    private function registerRecentPresentationsEndpoint()
    {
        $this->router->get("/\/ginger\/api\/v1\/recentPresentations/", function () {
            $ppts = [
                new Presentation(1, "name"),
                new Presentation(2, "name"),
            ];
            $presentationList = new PresentationList($ppts);

            return \json_encode($presentationList);
        });
    }

    private function registerPresentationEndpoint()
    {
        $this->router->get('/\/ginger\/api\/v1\/presentation\/[0-9]+/', function() {
            $ppt = new DetailedPresentation(1, "name", []);
            return \json_encode($ppt);
        });
    }
}
