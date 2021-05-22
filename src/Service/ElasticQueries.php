<?php

namespace App\Service;

use Elasticsearch\ClientBuilder;
use Symfony\Component\Config\Definition\Exception\Exception;

class ElasticQueries
{

    private $limit = 5;
    private $index = "kibana_sample_data_ecommerce";
    private $start_date;
    private $end_date;
    protected $client;

    public function __construct($start, $end)
    {
        $this->start_date = $start;
        $this->end_date = $end;

        $this->client = ClientBuilder::create()
            ->setElasticCloudId('i-o-optimized-deployment:ZWFzdHVzMi5henVyZS5lbGFzdGljLWNsb3VkLmNvbTo5MjQzJDMwNGFhODc3YjJmMTRlMDdhNzQ3N2I5Njg2MWYzOTM1JGQyYmY5OWYyOGE1YjRkMjg5YmNlYmNiZjdmMDZhYjdk')
            ->setBasicAuthentication('elastic', 'oWU9yRKrubPKri8Y6YllBRRg')
            ->build();
    }

    public function groupCityCountryData($page = 1)
    {
        $params = [];
        $params['index'] = $this->index;
        $params['body'] = [
            'size' => 0,
            'query' => [
                'range' => [
                    'order_date' => [
                        'gt' => $this->start_date,
                        'lt' => $this->end_date,
                    ],
                ],
            ],
            'aggs' => [
                'group_by_location' => [
                    'terms' => [
                        'field' => 'geoip.country_iso_code',
                    ],
                    'aggs' => [
                        'group_by_city' => [
                            'terms' => [
                                'field' => 'geoip.city_name',
                            ],
                            'aggs' => [
                                'product_price_stat' => [
                                    'stats' => [
                                        'field' => 'products.price',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ]
            ]
        ];

        $response = $this->client->search($params);
        if (!isset($response['aggregations']['group_by_location']['buckets'])) {
            throw new Exception("Invalid Response");
        }

        return $response['aggregations']['group_by_location']['buckets'];
    }

    public function getMostPopularSku()
    {
        $params = [];
        $params['index'] = $this->index;
        $params['body'] = [
            'size' => 0,
            'query' => [
                'range' => [
                    'order_date' => [
                        'gt' => $this->start_date,
                        'lt' => $this->end_date,
                    ],
                ],
            ],
            'aggs' => [
                'sku_purchases' => [
                    'terms' => [
                        'field' => 'sku',
                    ],
                ],
                'highest_sold_sku' => [
                    'max_bucket' => [
                        'buckets_path' => 'sku_purchases>_count',
                    ],
                ],

            ]
        ];

        $response = $this->client->search($params);

        if (!isset($response['aggregations']['highest_sold_sku'])) {
            throw new Exception("Invalid Response");
        }

        return $response['aggregations']['highest_sold_sku'];
    }


    public function getSkuBoughtTogether($sku, $page = 1)
    {
        $params = [];
        $params['index'] = $this->index;
        $params['body'] = [
            'size' => 0,
            'query' => [
                'bool' => [
                    'must' => [
                        0 => [
                            'match' => [
                                'sku' => $sku,
                            ],
                        ],
                        1 => [
                            'range' => [
                                'order_date' => [
                                    'gt' => $this->start_date,
                                    'lt' => $this->end_date,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            'aggs' => [
                'people_also_bought' => [
                    'terms' => [
                        'field' => 'sku',
                        'min_doc_count' => 1,
                        'exclude' => $sku,
                        'size' => '10000',
                    ],
                ],
            ],
        ];

        $response = $this->client->search($params);

        if (!isset($response['aggregations']['people_also_bought']['buckets'])) {
            throw new Exception("Invalid Response");
        }

        return $this->paginateResult($response['aggregations']['people_also_bought']['buckets'], $page);
    }


    private function paginateResult($arr, $page){
        if ($page == 1) {
            $from = 0;
        } else {
            $from = (($page - 1) * $this->limit);
        }

        return array_slice($arr, $page, $this->limit);
    }
}
