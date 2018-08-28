#!/usr/bin/env php
<?php

$_CC_DEBUG=0;
error_reporting(-1);

include 'lib/criteria-functions.php';

$options = parse_args();

$str = file_get_contents($options['file']);
$json = json_decode($str, true);

$currency_conversions_to_aud = json_decode(
  file_get_contents($options['currency']),
  true
);

csv_header();

foreach ( $json['data'] as $collection ) {
  foreach ( $collection['variations'] as $variation ) {
    if ($variation['shipping_height'] > 0) {

      $item_details = [
        'id'                             => $variation['id'],
        'ShippingPackagingAdjustmentPct' => 15,
        'ItemWeightKG'                   => unit_conv(weight_unit_map($collection['meta']['measurement']['value']),
                                                      $variation['shipping_weight']),
        'ItemLengthMtr'                  => unit_conv($collection['meta']['measurement']['value'],
                                                      $variation['shipping_length']),
        'ItemWidthMtr'                   => unit_conv($collection['meta']['measurement']['value'],
                                                      $variation['shipping_depth']),
        'ItemHeightMtr'                  => unit_conv($collection['meta']['measurement']['value'],
                                                      $variation['shipping_height']),
        'ItemHasWood'                    => $variation['has_wood'] ? 1 : 0,
        'MinimumOrder'                   => 1, # Not in data. Hardcoded for now
        'TailgateTruckRequired'          => 0, # 1 for yes, 0 for no. Not in data. Hardcoded for now
      ];

      $wholesale_price = currency_conv($collection['meta']['currency']['value'],
                                       $variation['wholesale_price']);
      $shipping_total  = ShippingTotal(0, $item_details, get_port_details($options['port']));

      csv_data([
        $variation['id'],
        $collection['meta']['brand']['name'],
        $collection['title'],
        get_variation_option($variation),
        $wholesale_price,
        round($shipping_total,2),
        round($wholesale_price + $shipping_total,2),
      ]);

      #print_r($collection);
    }
  }
};

function parse_args() {
  $options = getopt(NULL, array("file:", "currency:", "port:"));

  if (! isset($options['file']) ) {
    throw new Exception("You must specify the data file via --file=<filename>\n");
  }

  if (! isset($options['currency']) ) {
    $options['currency'] = 'currency.json';
  }

  if (! isset($options['port']) ) {
    $options['port'] = 'port.json';
  }

  return $options;
}

function csv_data ($data) {
  fputcsv(STDOUT, $data);
}

function csv_header() {
  csv_data([
    'id',
    'Brand',
    'Collection',
    'Variation',
    'Wholesale (AUD)',
    'Shipping (AUD)',
    'Retail (AUD)',
  ]);
}

function get_variation_option ($variation) {

  $option_arr = [];
  foreach ( $variation['options'] as $option ) {
    array_push($option_arr, $option['value']);
  }

  return implode(' ', $option_arr);
}

function currency_conv($unit, $value){

  # This conversion should be managed within the CMS
  # to allow Criteria to adjust the rate as required

  if (! isset($GLOBALS['currency_conversions_to_aud'][$unit]) ) {
    throw new Exception("Unhandled currency conversion, unit = '$unit'.\n");
  }

  return $value * $GLOBALS['currency_conversions_to_aud'][$unit];

};

function load_data($filename) {
  return json_decode(
    file_get_contents($filename),
    true
  );
}

?>
