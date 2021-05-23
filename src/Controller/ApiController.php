<?php

namespace App\Controller;

use App\Service\ElasticQueries;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;



class ApiController extends AbstractController
{
    
    /**
     * @Route("/api/test", name="testapi")
     */
    public function test(Request $request)
    {
        return new JsonResponse($request->query->get("start"));
    }


    /**
     * @Route("/api/ecom/order-by-location", name="ecom_order_by_location")
     */
    public function groupCityCountryData(Request $request){
        try {
            $start = $request->query->get("start_date");
            $end = $request->query->get("end_date");
    
            $t = new ElasticQueries($start, $end);
            return new jsonResponse($t->groupCityCountryData());

        } catch (\Throwable $th) {
            return new JsonResponse($th->getMessage());
        }
       
    }

    /**
     * @Route("/api/ecom/most-popular-sku", name="ecom_get_most_popular_sku")
     */
    public function getMostPopularSku(Request $request){
        try {
            $start = $request->query->get("start_date");
            $end = $request->query->get("end_date");
    
            $t = new ElasticQueries($start, $end);
            return new jsonResponse($t->getMostPopularSku());

        } catch (\Throwable $th) {
            return new JsonResponse($th->getMessage());
        }
       
    }

    /**
     * @Route("/api/ecom/sku-bought-together", name="ecom_get_sku_bought_together")
     */
    public function getSkuBoughtTogether(Request $request){
        try {
            $start = $request->query->get("start_date");
            $end = $request->query->get("end_date");
            $sku = $request->query->get("sku");
            $page = $request->query->get("page"); 
    
            $t = new ElasticQueries($start, $end);
            return new jsonResponse($t->getSkuBoughtTogether($sku, $page));

        } catch (\Throwable $th) {
            return new JsonResponse($th->getMessage());
        }
       
    }
    
}
