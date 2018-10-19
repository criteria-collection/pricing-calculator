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

      $ItemWholesalePriceAUD = currency_conv_to_aud(
        $collection['meta']['currency']['value'],
        $variation['wholesale_price']
      );

      $ItemInputs = [
        'id'                             => $variation['id'],
        'ItemWholesalePriceAUD'          => $ItemWholesalePriceAUD,
        'ItemCurrency'                   => $collection['meta']['currency']['value'],
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
        'MinimumOrder'                   => $minimum_order,
        'TailgateTruckRequired'          => 0, # 1 for yes, 0 for no. Not in data. Hardcoded for now
      ];

      $ShippingTotalAUD = ShippingTotal(
        $ItemInputs,
        $all_port_data['all'],
        $all_port_data['port'][$brand_port[$collection['meta']['brand']['slug']]],
          5, # ImportDutyPct
        0.5, # ShippingInsurancePct
        100, # ProductMarkupPct
         20, # ShippingMarkupPct
          2  # CreditCardSurchargePct
      );

      $ImportDutyTotalAUD = ImportDutyTotalAUD($ItemInputs);

      $InsuranceTotalAUD = InsuranceTotalAUD(
        $ItemInputs,
        $ShippingTotalAUD,
        .5 # ShippingInsurancePct
      );

      $RetailTotalAUD = RetailTotalAUD (
        $ItemInputs,
        100.0, # ProductMarkupPct,
        $ShippingTotalAUD,
        $ImportDutyTotalAUD,
        $InsuranceTotalAUD,
         20.0,  # ShippingMarkupPct,
          1.5 # CreditCardSurchargePct
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
        round($ItemWholesalePriceAUD,2),

        # shipping total AUD
        round($ShippingTotalAUD,2),

        # import duty in AUD
        round($ImportDutyTotalAUD,2),

        # insurance in AUD
        round($InsuranceTotalAUD,2),

        # retail in native currency
        round(
          currency_conv_from_aud(
            $collection['meta']['currency']['value'],
            $RetailTotalAUD
          ),2
        ),

        # retail in AUD
        round($RetailTotalAUD,2),
        ceilTo50($RetailTotalAUD),
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
    'Shipping (AUD)',
    'Duty (AUD)',
    'Insurance (AUD)',
    'Retail',
    'Retail (AUD)',
    'Retail next 50 (AUD)'
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
