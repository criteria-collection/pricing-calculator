#!/usr/bin/env php
<?php

include 'criteria-functions.php';

$str = file_get_contents('test-data/CC_collection_dump_9418.json');

$json = json_decode($str, true);

foreach ( $json['data'] as $product ) {
  foreach ( $product['variations'] as $variation ) {
    if ($variation['shipping_height'] > 0) {

      $item_details = [
        'ShippingPackagingAdjustmentPct' => 15,
        'ItemWeightKG'                   => $variation['shipping_weight'],
        'ItemLengthMtr'                  => $variation['shipping_length'],
        'ItemWidthMtr'                   => $variation['shipping_depth'],
        'ItemHeightMtr'                  => $variation['shipping_height'],
        'MinimumOrder'                   => 1,
        'TailgateTruckRequired'          => 0, # 1 for yes, 0 for no
      ];

      echo $product['meta']['brand']['name']
      . ' - ' .$product['title']
      . ' - ' . ShippingTotal(0, $item_details, get_port_details())
      . "\n";
      #print_r($product);
    }
  }
};


function get_port_details() {
  return
  [
    'domestic' => [
      'ShippingDomesticCollectionMin'        => 1,
      'ShippingDomesticCollectionPerM3'      => 2,
      'ShippingDomesticDelivery'             => 3,
      'ShippingDomesticDeliverySurchargePct' => 4,
    ],
    'international' => [
      'lcl' => [
        'ShippingLCL_Collection_Min'        => 1,
        'ShippingLCL_Collection_MT'         => 2,
        'ShippingLCLPerItem'                => 3,
        'ShippingLCL_Delivery_Min'          => 4,
        'ShippingLCL_Delivery_WV'           => 5,
        'ShippingLCL_DeliverySurchargePct'  => 6,
        'ShippingLCL_DeliveryTailgateTruck' => 7,
        'ShippingLCLPerWV'                  => 8,
      ],
      'af' => [
        'ShippingAF_Collection_Min'        =>  1,
        'ShippingAF_Collection_CW'         =>  2,
        'ShippingAFPerItem'                =>  3,
        'ShippingAF_THC_Min'               =>  4,
        'ShippingAF_THC_CW'                =>  5,
        'ShippingAF_WarRisk_Min'           =>  6,
        'ShippingAF_WarRisk_CW'            =>  7,
        'ShippingAF_Security_CW'           =>  8,
        'ShippingAF_Freight_Min'           =>  9,
        'ShippingAF_Freight_CW'            => 10,
        'ShippingAF_Fuel_Min'              => 11,
        'ShippingAF_Fuel_CW'               => 12,
        'ShippingAF_ITF_Min'               => 13,
        'ShippingAF_ITF_MT'                => 14,
        'ShippingAF_Handling_Min'          => 15,
        'ShippingAF_Handling_MT'           => 16,
        'ShippingAF_Delivery_Min'          => 17,
        'ShippingAF_Delivery_WV'           => 18,
        'ShippingAF_DeliverySurchargePct'  => 19,
        'ShippingAF_DeliveryTailgateTruck' => 20,
      ]
    ]
  ];
}

?>
