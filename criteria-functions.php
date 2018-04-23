<?php

$DEBUG=0;
error_reporting(-1);

function warn ($message) {
  global $DEBUG;
  if ($DEBUG) {
    $message = $message . "\n";
    fwrite(STDERR, $message);
  }
}

function ShippingInternational($ItemInputs, $PortInputs) {
  $BestPrice = min(
    ShippingLCLTotal($ItemInputs, $PortInputs['lcl']),
    ShippingAFTotal($ItemInputs, $PortInputs['af'])
  );

  $CustomsQuarantineInspection;
  if ($ItemInputs['ItemHasWood']) {

    $CustomsQuarantineInspection = max(
      $PortInputs['all']['CustomsQuarantineInspectionWoodMin'],
      $PortInputs['all']['CustomsQuarantineInspectionWoodPerM3']
      * $ItemInputs['ShippedItemVolumeM3']
    );
  }
  else {
    $CustomsQuarantineInspection =
      $PortInputs['all']['CustomsQuarantineInspectionNoWood'];
  }

return $BestPrice
       + $PortInputs['all']['CustomsQuarantinePerItem']
       + $CustomsQuarantineInspection;
}

function ShippingLCLTotal($ItemInputs, $PortLCLInputs) {

  foreach ( $PortLCLInputs as $key=>$value ) {
    warn($key . ' => ' . $value);
  };

  $ShippingLCL_Collection = max(
    $PortLCLInputs['ShippingLCL_Collection_Min'],
    $PortLCLInputs['ShippingLCL_Collection_MT']
    * $ItemInputs['ShippedItemWeightMT']
  );

  $ShippingLCL_DeliverySurcharge =
    1+($PortLCLInputs['ShippingLCL_DeliverySurchargePct']/100);

  $ShippingLCL_Delivery = max(
    $PortLCLInputs['ShippingLCL_Delivery_Min'],
    $PortLCLInputs['ShippingLCL_Delivery_WV']
    * $ItemInputs['ShippedItemWV']
  ) * $ShippingLCL_DeliverySurcharge
  + $ItemInputs['TailgateTruckRequired']
    ? $PortLCLInputs['ShippingLCL_DeliveryTailgateTruck']
    : 0;

  return
    $PortLCLInputs['ShippingLCLPerItem'] +
    $ShippingLCL_Collection +
    $ShippingLCL_Delivery +
    $PortLCLInputs['ShippingLCLPerWV']
    * $ItemInputs['ShippedItemWV'];

}

function ShippingAFTotal($ItemInputs, $PortAFInputs) {

  foreach ( $PortAFInputs as $key=>$value ) {
    warn($key . ' => ' . $value);
  };

  $ShippingAF_Collection = max(
    $PortAFInputs['ShippingAF_Collection_Min'],
    $PortAFInputs['ShippingAF_Collection_CW']
    * $ItemInputs['ShippedItemCW']
  );

  $ShippingAF_THC = max(
    $PortAFInputs['ShippingAF_THC_Min'],
    $PortAFInputs['ShippingAF_THC_CW']
    * $ItemInputs['ShippedItemCW']
  );

  $ShippingAF_WarRisk = max(
    $PortAFInputs['ShippingAF_WarRisk_Min'],
    $PortAFInputs['ShippingAF_WarRisk_CW']
    * $ItemInputs['ShippedItemCW']
  );

  $ShippingAF_Security =
    $PortAFInputs['ShippingAF_Security_CW']
    * $ItemInputs['ShippedItemCW'];

  $ShippingAF_Freight = max(
    $PortAFInputs['ShippingAF_Freight_Min'],
    $PortAFInputs['ShippingAF_Freight_CW']
    * $ItemInputs['ShippedItemCW']
  );

  $ShippingAF_Fuel = max(
    $PortAFInputs['ShippingAF_Fuel_Min'],
    $PortAFInputs['ShippingAF_Fuel_CW']
    * $ItemInputs['ShippedItemCW']
  );

  $ShippingAF_ITF = max(
    $PortAFInputs['ShippingAF_ITF_Min'],
    $PortAFInputs['ShippingAF_ITF_MT']
    * $ItemInputs['ShippedItemWeightMT']
  );

  $ShippingAF_Handling = max(
    $PortAFInputs['ShippingAF_Handling_Min'],
    $PortAFInputs['ShippingAF_Handling_MT']
    * $ItemInputs['ShippedItemWeightMT']
  );

  $ShippingAF_DeliverySurcharge =
    1 + ($PortAFInputs['ShippingAF_DeliverySurchargePct']/100);

  $ShippingAF_Delivery = max(
    $PortAFInputs['ShippingAF_Delivery_Min'],
    $PortAFInputs['ShippingAF_Delivery_WV']
    * $ItemInputs['ShippedItemWV']
  ) * $ShippingAF_DeliverySurcharge
  + $ItemInputs['TailgateTruckRequired']
    ? $PortAFInputs['ShippingAF_DeliveryTailgateTruck']
    : 0;

  return
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
}

function ShippingDomestic($ItemInputs, $PortDFInputs) {

  $ShippingDomesticCollection = max(
    $PortDFInputs['ShippingDomesticCollectionMin'],
    $PortDFInputs['ShippingDomesticCollectionPerM3']
    * $ItemInputs['ShippedItemVolumeM3']
  );

  $ShippingDomesticDeliverySurcharge =
    1 + ($PortDFInputs['ShippingDomesticDeliverySurchargePct']/100);

  return $PortDFInputs['ShippingDomesticDelivery'] +
    $ShippingDomesticCollection
    * $ShippingDomesticDeliverySurcharge;

}

function ShippingTotal($Domestic, $ItemInputs, $PortInputs) {

  $ItemInputs['ShippingPackagingAdjustment'] =
    1+($ItemInputs['ShippingPackagingAdjustmentPct']/100);

  $ItemInputs['ItemVolumeM3'] =
    $ItemInputs['ItemLengthMtr'] * $ItemInputs['ItemWidthMtr'] * $ItemInputs['ItemHeightMtr'];

  $ItemInputs['ShippedItemWeightMT'] =
    ($ItemInputs['ItemWeightKG'] * $ItemInputs['MinimumOrder'] / 1000)
    * $ItemInputs['ShippingPackagingAdjustment'];

  $ItemInputs['ShippedItemVolumeM3'] =
    $ItemInputs['ItemVolumeM3']
    * $ItemInputs['MinimumOrder']
    * $ItemInputs['ShippingPackagingAdjustment'];

  $ItemInputs['ShippedItemWV'] = max(
    $ItemInputs['ShippedItemWeightMT'],
    $ItemInputs['ShippedItemVolumeM3']
  );

  $ItemInputs['ShippedItemVolumetricWeight'] =
    $ItemInputs['ShippedItemVolumeM3'] * 167 / 1000;

  $ItemInputs['ShippedItemCW'] = max(
    $ItemInputs['ShippedItemVolumetricWeight'],
    $ItemInputs['ShippedItemWeightMT']
  );

  foreach ( $ItemInputs as $key=>$value ) {
    warn($key . ' => ' . $value);
  };

  return $Domestic
    ? ShippingDomestic($ItemInputs, $PortInputs['domestic'])
    : ShippingInternational($ItemInputs, $PortInputs['international']);
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

  # TODO confirm this is the associated unit.
  if ($unit == 'mm') {
    return 'kg';
  }

  throw new Exception("Unhandled unit type conversion, unit = '$unit'.\n");

};

?>
