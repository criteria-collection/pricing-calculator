#!/usr/bin/env php
<?php

$_CC_DEBUG=0;
error_reporting(-1);

include 'lib/criteria-functions.php';

# Parse command line
$options = parse_args();

# Get collection data
$coll_data = load_data($options['file']);

# Get map from brand-slug to port
$brand_port = load_data('test-data/brand-port.json');

# Get currency conversions
$currency_conversions_to_aud = load_data($options['currency']);

# Load port data
$all_port_data = load_data($options['ports']);

csv_header();

foreach ( $coll_data['data'] as $collection ) {

  $minimum_order = $collection['order']['minimum_order']
                   ? $collection['order']['minimum_order']
                   : 1;

  foreach ( $collection['variations'] as $variation ) {

    # Exclude variations with insufficient data.
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
        'MinimumOrder'                   => $minimum_order, # Not in data. Hardcoded for now
        'TailgateTruckRequired'          => 0, # 1 for yes, 0 for no. Not in data. Hardcoded for now
      ];

      $shipping_total_aud = ShippingTotal(
        $item_details,
        $all_port_data['all'],
        $all_port_data['port'][$brand_port[$collection['meta']['brand']['slug']]]
      );

      $wholesale_price_aud = currency_conv_to_aud(
        $collection['meta']['currency']['value'],
        $variation['wholesale_price']
      );

      csv_data([
        $variation['id'],
        $collection['meta']['brand']['name'],
        $collection['title'],
        get_variation_option($variation),
        # currency
        $collection['meta']['currency']['value'],
        # wholesale price in native currency
        $variation['wholesale_price'],
        # wholesale price AUD
        $wholesale_price_aud,
        # shipping total in native currency
        round(currency_conv_from_aud(
          $collection['meta']['currency']['value'],
          $shipping_total_aud
        ),2),
        # shipping total AUD
        round($shipping_total_aud,2),
        # retail in native currency
        round(
          $variation['wholesale_price'] +
          currency_conv_from_aud(
            $collection['meta']['currency']['value'],
            $shipping_total_aud
          ),2
        ),
        # retail AUD
        round(
          currency_conv_to_aud(
            $collection['meta']['currency']['value'],
            $variation['wholesale_price']
          ) +
          $shipping_total_aud
          ,2
        )
      ]);
      #print_r($collection);
    }
  }
};

function parse_args() {
  $options = getopt(NULL, array("file:", "currency:", "ports:"));

  if (! isset($options['file']) ) {
    $options['file'] = 'test-data/export.json';
  }

  if (! isset($options['currency']) ) {
    $options['currency'] = 'test-data/currency.json';
  }

  if (! isset($options['ports']) ) {
    $options['ports'] = 'test-data/ports.json';
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
    'Currency',
    'Wholesale',
    'Wholesale (AUD)',
    'Shipping',
    'Shipping (AUD)',
    'Retail',
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

function currency_conv_to_aud($unit, $value){

  # This conversion should be managed within the CMS
  # to allow Criteria to adjust the rate as required

  if (! isset($GLOBALS['currency_conversions_to_aud'][$unit]) ) {
    throw new Exception("Unhandled currency conversion, unit = '$unit'.\n");
  }

  return $value * $GLOBALS['currency_conversions_to_aud'][$unit];

};

function currency_conv_from_aud($unit, $value){

  # This conversion should be managed within the CMS
  # to allow Criteria to adjust the rate as required

  if (! isset($GLOBALS['currency_conversions_to_aud'][$unit]) ) {
    throw new Exception("Unhandled currency conversion, unit = '$unit'.\n");
  }

  return $value * (1/$GLOBALS['currency_conversions_to_aud'][$unit]);

};

function load_data($filename) {
  return json_decode(
    file_get_contents($filename),
    true
  );
}

?>
