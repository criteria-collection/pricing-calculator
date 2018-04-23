#!/usr/bin/env php
<?php

include 'criteria-functions.php';

$str = file_get_contents('test-data/CC_collection_dump_9418.json');

$json = json_decode($str, true);

foreach ( $json['data'] as $product ) {
  foreach ( $product['variations'] as $variation ) {
fputcsv(STDOUT,[
  'id',
  'Brand',
  'Collection',
  'Variation',
  'Wholesale (AUD)',
  'Shipping (AUD)',
  'Retail (AUD)',
]);

foreach ( $json['data'] as $collection ) {
  foreach ( $collection['variations'] as $variation ) {
    if ($variation['shipping_height'] > 0) {

      $item_details = [
        'ShippingPackagingAdjustmentPct' => 15,
        'ItemWeightKG'                   => $variation['shipping_weight'],
        'ItemLengthMtr'                  => $variation['shipping_length'],
        'ItemWidthMtr'                   => $variation['shipping_depth'],
        'ItemHeightMtr'                  => $variation['shipping_height'],
        'ItemHasWood'                    => $variation['has_wood'] ? 1 : 0,
        'MinimumOrder'                   => 1, # Not in data hardcoded for now
        'TailgateTruckRequired'          => 0, # 1 for yes, 0 for no. Hardcoded for now
      ];

      echo $product['meta']['brand']['name']
      . ' - ' .$product['title']
      . ' - ' . ShippingTotal(0, $item_details, get_port_details())
      . "\n";
      #print_r($product);
      fputcsv(STDOUT,[
        $variation['id'],
        $collection['meta']['brand']['name'],
        $collection['title'],
        get_variation_option($variation),
        $wholesale_price,
        round($shipping_total,2),
        round($wholesale_price + $shipping_total,2),
      ]);
    }
  }
};

function get_port_details() {
  return
  # DB Shecher ex Brooklyn
  [
    'domestic' => [
      'ShippingDomesticCollectionMin'        => 1,
      'ShippingDomesticCollectionPerM3'      => 2,
      'ShippingDomesticDelivery'             => 3,
      'ShippingDomesticDeliverySurchargePct' => 4,
    ],
    'international' => [
      'all' => [
        'CustomsQuarantinePerItem'             => 106.20,
        'CustomsQuarantineInspectionNoWood'    =>  40.00,
        'CustomsQuarantineInspectionWoodMin'   => 130.00,
        'CustomsQuarantineInspectionWoodPerM3' => 150.00,

      ],
      'lcl' => [
        'ShippingLCL_Collection_Min'        => 326.40,
        'ShippingLCL_Collection_MT'         => 369.4545455,
        'ShippingLCLPerItem'                => 648.00,
        'ShippingLCL_Delivery_Min'          =>  85.00,
        'ShippingLCL_Delivery_WV'           =>  25.00,
        'ShippingLCL_DeliverySurchargePct'  =>  16.00,
        'ShippingLCL_DeliveryTailgateTruck' => 130.00,
        'ShippingLCLPerWV'                  => 367.30,
      ],
      'af' => [
        'ShippingAFPerItem'                =>  455.56,
        'ShippingAF_Collection_Min'        =>   89.60,
        'ShippingAF_Collection_CW'         =>  486.40,
        'ShippingAF_THC_Min'               =>  115.20,
        'ShippingAF_THC_CW'                =>  192.00,
        'ShippingAF_WarRisk_Min'           =>    0.00,
        'ShippingAF_WarRisk_CW'            =>  204.80,
        'ShippingAF_Security_CW'           =>    0.00,
        'ShippingAF_Freight_Min'           =>  160.00,
        'ShippingAF_Freight_CW'            => 4992.00,
        'ShippingAF_Fuel_Min'              =>    0.00,
        'ShippingAF_Fuel_CW'               => 1600.00,
        'ShippingAF_ITF_Min'               =>  100.00,
        'ShippingAF_ITF_MT'                =>  250.00,
        'ShippingAF_Handling_Min'          =>   47.00,
        'ShippingAF_Handling_MT'           =>  470.00,
        'ShippingAF_Delivery_Min'          =>   50.00,
        'ShippingAF_Delivery_WV'           =>  320.00,
        'ShippingAF_DeliveryTailgateTruck' =>  130.00,
        'ShippingAF_DeliverySurchargePct'  =>   17.00,
      ]
    ]
  ];
}

?>
