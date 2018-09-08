<?php

error_reporting(-1);

function ShippingInternational($ItemInputs, $PortInputsAll, $PortInputs) {

  foreach ( $PortInputsAll as $key=>$value ) {
    calc_log($ItemInputs, $key, $value, 'input');
  }

  $ShippingLCLTotal = ShippingLCLTotal($ItemInputs, $PortInputs['lcl']);
  $ShippingAFTotal  = ShippingAFTotal($ItemInputs, $PortInputs['af']);
  $ShippingIACTotal = ShippingIACTotal($ItemInputs, $PortInputs['iac']);

  $BestPrice = min(
    $ShippingLCLTotal,
    $ShippingAFTotal,
    $ShippingIACTotal
  );

  calc_log($ItemInputs, 'BestPrice', $BestPrice, NULL );

  $price_to_method = array(
    $ShippingLCLTotal => 'LCL',
    $ShippingAFTotal  => 'AF',
    $ShippingIACTotal => 'IAC'
  );

  $BestMethod = $price_to_method[$BestPrice];

  calc_log($ItemInputs, 'BestMethod', $BestMethod, NULL );

  $CustomsQuarantineInspection;
  if ($ItemInputs['ItemHasWood']) {

    $CustomsQuarantineInspection = max(
      $PortInputsAll['CustomsQuarantineInspectionWoodMin'],
      $PortInputsAll['CustomsQuarantineInspectionWoodPerM3']
      * $ItemInputs['ShippedItemVolumeM3']
    );
  }
  else {
    $CustomsQuarantineInspection =
      $PortInputsAll['CustomsQuarantineInspectionNoWood'];
  }
  calc_log($ItemInputs, 'CustomsQuarantineInspection', $CustomsQuarantineInspection, NULL );

  $ShippingInternational = $BestPrice
       + $PortInputsAll['CustomsQuarantinePerItem']
       + $CustomsQuarantineInspection;
  calc_log($ItemInputs, 'ShippingInternational', $ShippingInternational, NULL );

  return $ShippingInternational;

}

function ShippingLCLTotal($ItemInputs, $PortLCLInputs) {

  foreach ( $PortLCLInputs as $key=>$value ) {
    calc_log($ItemInputs, $key, $value, 'input');
  }

  $ShippingLCL_Collection = max(
    $PortLCLInputs['ShippingLCL_Collection_Min'],
    $PortLCLInputs['ShippingLCL_Collection_MT']
    * $ItemInputs['ShippedItemWeightMT']
  );
  calc_log($ItemInputs, 'ShippingLCL_Collection', $ShippingLCL_Collection, NULL );

  $ShippingLCL_DeliverySurcharge =
    1+($PortLCLInputs['ShippingLCL_DeliverySurchargePct']/100);
  calc_log($ItemInputs, 'ShippingLCL_DeliverySurcharge', $ShippingLCL_DeliverySurcharge, NULL );

  $ShippingLCL_Delivery = max(
    $PortLCLInputs['ShippingLCL_Delivery_Min'],
    $PortLCLInputs['ShippingLCL_Delivery_WV']
    * $ItemInputs['ShippedItemWV']
  ) * $ShippingLCL_DeliverySurcharge
  + $ItemInputs['TailgateTruckRequired']
    ? $PortLCLInputs['ShippingLCL_DeliveryTailgateTruck']
    : 0;
  calc_log($ItemInputs, 'ShippingLCL_Delivery', $ShippingLCL_Delivery, NULL );

  $ShippingLCLTotal =
    $PortLCLInputs['ShippingLCLPerItem'] +
    $ShippingLCL_Collection +
    $ShippingLCL_Delivery +
    $PortLCLInputs['ShippingLCLPerWV']
    * $ItemInputs['ShippedItemWV'];
  calc_log($ItemInputs, 'ShippingLCLTotal', $ShippingLCLTotal, NULL );

  return $ShippingLCLTotal;

}

function ShippingAFTotal($ItemInputs, $PortAFInputs) {

  foreach ( $PortAFInputs as $key=>$value ) {
    calc_log($ItemInputs, $key, $value, 'input');
  }

  $ShippingAF_Collection = max(
    $PortAFInputs['ShippingAF_Collection_Min'],
    $PortAFInputs['ShippingAF_Collection_CW']
    * $ItemInputs['ShippedItemCW']
  );
  calc_log($ItemInputs, 'ShippingAF_Collection', $ShippingAF_Collection, NULL );

  $ShippingAF_THC = max(
    $PortAFInputs['ShippingAF_THC_Min'],
    $PortAFInputs['ShippingAF_THC_CW']
    * $ItemInputs['ShippedItemCW']
  );
  calc_log($ItemInputs, 'ShippingAF_THC', $ShippingAF_THC, NULL );

  $ShippingAF_WarRisk = max(
    $PortAFInputs['ShippingAF_WarRisk_Min'],
    $PortAFInputs['ShippingAF_WarRisk_CW']
    * $ItemInputs['ShippedItemCW']
  );
  calc_log($ItemInputs, 'ShippingAF_WarRisk', $ShippingAF_WarRisk, NULL );

  $ShippingAF_Security = max(
    $PortAFInputs['ShippingAF_Security_Min'],
    $PortAFInputs['ShippingAF_Security_CW']
    * $ItemInputs['ShippedItemCW']
  );
  calc_log($ItemInputs, 'ShippingAF_Security', $ShippingAF_Security, NULL );

  $ShippingAF_Freight = max(
    $PortAFInputs['ShippingAF_Freight_Min'],
    $PortAFInputs['ShippingAF_Freight_CW']
    * $ItemInputs['ShippedItemCW']
  );
  calc_log($ItemInputs, 'ShippingAF_Freight', $ShippingAF_Freight, NULL );

  $ShippingAF_Fuel = max(
    $PortAFInputs['ShippingAF_Fuel_Min'],
    $PortAFInputs['ShippingAF_Fuel_CW']
    * $ItemInputs['ShippedItemCW']
  );
  calc_log($ItemInputs, 'ShippingAF_Fuel', $ShippingAF_Fuel, NULL );

  $ShippingAF_ITF = max(
    $PortAFInputs['ShippingAF_ITF_Min'],
    $PortAFInputs['ShippingAF_ITF_MT']
    * $ItemInputs['ShippedItemWeightMT']
  );
  calc_log($ItemInputs, 'ShippingAF_ITF', $ShippingAF_ITF, NULL );

  $ShippingAF_Handling = max(
    $PortAFInputs['ShippingAF_Handling_Min'],
    $PortAFInputs['ShippingAF_Handling_MT']
    * $ItemInputs['ShippedItemWeightMT']
  );
  calc_log($ItemInputs, 'ShippingAF_Handling', $ShippingAF_Handling, NULL );

  $ShippingAF_DeliverySurcharge =
    1 + ($PortAFInputs['ShippingAF_DeliverySurchargePct']/100);
  calc_log($ItemInputs, 'ShippingAF_DeliverySurcharge', $ShippingAF_DeliverySurcharge, NULL );

  $ShippingAF_Delivery = max(
    $PortAFInputs['ShippingAF_Delivery_Min'],
    $PortAFInputs['ShippingAF_Delivery_WV']
    * $ItemInputs['ShippedItemWV']
  ) * $ShippingAF_DeliverySurcharge
  + $ItemInputs['TailgateTruckRequired']
    ? $PortAFInputs['ShippingAF_DeliveryTailgateTruck']
    : 0;
  calc_log($ItemInputs, 'ShippingAF_Delivery', $ShippingAF_Delivery, NULL );

  $ShippingAFTotal =
    $PortAFInputs['ShippingAFPerItem'] +
    $ShippingAF_Collection +
    $ShippingAF_THC +
    $ShippingAF_WarRisk +
    $ShippingAF_Security +
    $ShippingAF_Freight +
    $ShippingAF_Fuel +
    $ShippingAF_ITF +
    $ShippingAF_Handling +
    $ShippingAF_Delivery;
  calc_log($ItemInputs, 'ShippingAFTotal', $ShippingAFTotal, NULL );

  return $ShippingAFTotal;
}

function ShippingIACTotal($ItemInputs, $PortIACInputs) {

  foreach ( $PortIACInputs as $key=>$value ) {
    calc_log($ItemInputs, $key, $value, 'input');
  }

  $ShippingIAC_CW = max(
    $PortIACInputs['ShippingIAC_WeightKG_Min'],
    $PortIACInputs['ShippingIAC_VolumeConversion'] * $ItemInputs['ShippedItemVolumeM3'],
    $ItemInputs['ShippedItemWeightKG']
  );
  calc_log($ItemInputs, 'ShippingIAC_CW', $ShippingIAC_CW, NULL );

  $ShippingIAC_CustomsChargeTotal =
    $ItemInputs['ItemWholesalePriceAUD'] > $PortIACInputs['ShippingIAC_CustomsThreshhold']
    ? $PortIACInputs['ShippingIAC_CustomsCharge']
    : 0;
  calc_log($ItemInputs, 'ShippingIAC_CustomsChargeTotal', $ShippingIAC_CustomsChargeTotal, NULL);

  $ShippingIAC_FxTotal =
    $PortIACInputs['ShippingIAC_Fx_Offset']
    + ($PortIACInputs['ShippingIAC_Fx_Multiplier'] * $ShippingIAC_CW);
  calc_log($ItemInputs, 'ShippingIAC_FxTotal', $ShippingIAC_FxTotal, NULL);

  $ShippingIAC_DeliveryPreFuel =
    $ShippingIAC_FxTotal
    + $PortIACInputs['ShippingIACPerItem']
    + $ShippingIAC_CustomsChargeTotal ;
  calc_log($ItemInputs, 'ShippingIAC_DeliveryPreFuel', $ShippingIAC_DeliveryPreFuel, NULL);

  $ShippingIAC_DeliverySurcharge =
    1 + ($PortIACInputs['ShippingIAC_DeliverySurchargePct']/100);
  calc_log($ItemInputs, 'ShippingIAC_DeliverySurcharge', $ShippingIAC_DeliverySurcharge, NULL );

  $ShippingIACTotal = $ShippingIAC_DeliveryPreFuel * $ShippingIAC_DeliverySurcharge;
  calc_log($ItemInputs, 'ShippingIACTotal', $ShippingIACTotal, NULL );

  return $ShippingIACTotal;
}

function ShippingDomestic($ItemInputs, $PortDFInputs) {

  foreach ( $PortDFInputs as $key=>$value ) {
    calc_log($ItemInputs, $key, $value, 'input');
  }

  $ShippingDomesticCollection = max(
    $PortDFInputs['ShippingDomesticCollectionMin'],
    $PortDFInputs['ShippingDomesticCollectionPerM3']
    * $ItemInputs['ShippedItemVolumeM3']
  );
  calc_log($ItemInputs, 'ShippingDomesticCollection', $ShippingDomesticCollection, NULL );

  $ShippingDomesticDeliverySurcharge =
    1 + ($PortDFInputs['ShippingIAC_DeliverySurchargePct']/100);
  calc_log($ItemInputs, 'ShippingDomesticDeliverySurcharge', $ShippingDomesticDeliverySurcharge, NULL );

  $ShippingDomestic = $PortDFInputs['ShippingDomesticDelivery'] +
    $ShippingDomesticCollection
    * $ShippingDomesticDeliverySurcharge;
  calc_log($ItemInputs, 'ShippingDomestic', $ShippingDomestic, NULL );

  return $ShippingDomestic;
}

function ShippingTotal($ItemInputs, $PortInputsAll, $PortInputs) {

  calc_log($ItemInputs,'Domestic', $PortInputs['domestic'], 'input');

  foreach ( $ItemInputs as $key=>$value ) {
    calc_log($ItemInputs, $key, $value, 'input');
  }

  $ItemInputs['ShippingPackagingAdjustment'] =
    1+($ItemInputs['ShippingPackagingAdjustmentPct']/100);
  calc_log($ItemInputs,'ShippingPackagingAdjustment', NULL, NULL);

  $ItemInputs['ItemVolumeM3'] =
    $ItemInputs['ItemLengthMtr'] * $ItemInputs['ItemWidthMtr'] * $ItemInputs['ItemHeightMtr'];
  calc_log($ItemInputs,'ItemVolumeM3', NULL, NULL);

  $ItemInputs['ShippedItemWeightMT'] =
    ($ItemInputs['ItemWeightKG'] * $ItemInputs['MinimumOrder'] / 1000)
    * $ItemInputs['ShippingPackagingAdjustment'];
  calc_log($ItemInputs,'ShippedItemWeightMT', NULL, NULL);

  $ItemInputs['ShippedItemWeightKG'] =
    ($ItemInputs['ItemWeightKG'] * $ItemInputs['MinimumOrder'])
    * $ItemInputs['ShippingPackagingAdjustment'];
  calc_log($ItemInputs,'ShippedItemWeightKG', NULL, NULL);

  $ItemInputs['ShippedItemVolumeM3'] =
    $ItemInputs['ItemVolumeM3']
    * $ItemInputs['MinimumOrder']
    * $ItemInputs['ShippingPackagingAdjustment'];
  calc_log($ItemInputs,'ShippedItemVolumeM3', NULL, NULL);

  $ItemInputs['ShippedItemWV'] = max(
    $ItemInputs['ShippedItemWeightMT'],
    $ItemInputs['ShippedItemVolumeM3']
  );
  calc_log($ItemInputs,'ShippedItemWV', NULL, NULL);

  $ItemInputs['ShippedItemVolumetricWeight'] =
    $ItemInputs['ShippedItemVolumeM3'] * 167 / 1000;
  calc_log($ItemInputs,'ShippedItemVolumetricWeight', NULL, NULL);

  $ItemInputs['ShippedItemCW'] = max(
    $ItemInputs['ShippedItemVolumetricWeight'],
    $ItemInputs['ShippedItemWeightMT']
  );
  calc_log($ItemInputs,'ShippedItemCW', NULL, NULL);

  $ShippingTotal = $PortInputs['domestic']
    ? ShippingDomestic($ItemInputs, $PortInputs)
    : ShippingInternational(
        $ItemInputs,
        $PortInputsAll['international'],
        $PortInputs
    );
  calc_log($ItemInputs,'ShippingTotal', $ShippingTotal, NULL);

  return $ShippingTotal;
}

function ImportDutyTotalAUD ($ItemInputs) {

  $ImportDuty = ImportDuty($ItemInputs['ItemCurrency']);
  calc_log($ItemInputs,'ImportDuty', $ImportDuty, NULL);

  $ImportDutyTotalAUD = $ItemInputs['ItemWholesalePriceAUD'] * $ImportDuty;
  calc_log($ItemInputs,'ImportDutyTotalAUD', $ImportDutyTotalAUD, NULL);

  return $ImportDutyTotalAUD;
}

function InsuranceTotalAUD (
  $ItemInputs,
  $ShippingTotalAUD,
  $ShippingInsurancePct
) {

  $InsuranceTotalAUD =
    ($ItemInputs['ItemWholesalePriceAUD'] + $ShippingTotalAUD)
    * pct_multiplier($ShippingInsurancePct);

  calc_log($ItemInputs,'ShippingInsurancePct', $ShippingInsurancePct, NULL);
  calc_log($ItemInputs,'InsuranceTotalAUD', $InsuranceTotalAUD, NULL);

  return $InsuranceTotalAUD;
}

function RetailTotalAUD (
  $ItemInputs,
  $ProductMarkupPct,
  $ShippingTotalAUD,
  $ImportDutyTotalAUD,
  $InsuranceTotalAUD,
  $ShippingMarkupPct,
  $CreditCardSurchargePct
) {

  calc_log(NULL,'ProductMarkupPct', $ProductMarkupPct, NULL);
  calc_log(NULL,'ShippingMarkupPct', $ShippingMarkupPct, NULL);
  calc_log(NULL,'CreditCardSurchargePct', $CreditCardSurchargePct, NULL);

  $RetailTotalAUD = (
    ($ItemInputs['ItemWholesalePriceAUD'] * pct_multiplier($ProductMarkupPct)) +
    (($ShippingTotalAUD + $ImportDutyTotalAUD + $InsuranceTotalAUD) * pct_multiplier($ShippingMarkupPct))
  ) * pct_multiplier($CreditCardSurchargePct);

  calc_log(NULL,'RetailTotalAUD', $RetailTotalAUD, NULL);

  return $RetailTotalAUD;
}

function ImportDutyPct($currency) {

  switch ($currency) {
      case "AUD":
        $ImportDutyPct = 0;
        break;
      case "CAD":
        $ImportDutyPct = 0;
        break;
      case "USD":
        $ImportDutyPct = 0;
        break;
      default:
        $ImportDutyPct = 5; # Default import duty
  }

  return $ImportDutyPct;

}

function ImportDuty($currency) {
  $ImportDutyPct = ImportDutyPct($currency);
  return 1 + ($ImportDutyPct/100);
}

function unit_conv($unit, $value){

  if ($unit == 'in') {
    return $value * 0.0254;
  }

  if ($unit == 'mm') {
    return $value * 0.001;
  }

  if ($unit == 'lb') {
    return $value * 0.45359237;
  }

  if ($unit == 'kg') {
    return $value;
  }

  throw new Exception("Unhandled conversion, unit = '$unit'.\n");

};

function weight_unit_map($unit){

  if ($unit == 'in') {
    return 'lb';
  }

  if ($unit == 'mm') {
    return 'kg';
  }

  throw new Exception("Unhandled unit type conversion, unit = '$unit'.\n");

};

function pct_multiplier($pct) {
  return 1 + ($pct / 100);
}

function calc_log ($item, $calculation, $calc_result, $note) {

  global $_CC_DEBUG;
  if ( ! $_CC_DEBUG && ! getenv('_CC_DEBUG') ) {
    return;
  }

  if (! isset($calc_result)) {
    $calc_result = $item[$calculation];
  }

  if (! isset($note)) {
    $note = 'calculation';
  }

  fputcsv(STDERR, array($item['id'], $calculation, $calc_result, $note));

}

function warn ($message) {
  $message = $message . "\n";
  fwrite(STDERR, $message);
}

?>
