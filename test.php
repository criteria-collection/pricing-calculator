#!/usr/bin/env php
<?php

include 'criteria-functions.php';

echo "Shipping total: " . ShippingTotal(
    1, # 1 for domestic, 0 for international
    [
      'ShippingPackagingAdjustmentPct' => 1,
      'ItemWeightKG'                   => 2,
      'ItemLengthMtr'                  => 3,
      'ItemWidthMtr'                   => 4,
      'ItemHeightMtr'                  => 5,
      'MinimumOrder'                   => 6,
      'TailgateTruckRequired'          => 1, # 1 for yes, 0 for no
    ],
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
  ]
);

?>
