# Testing

`_CC_DEBUG=1 ./test.php --file=test-data/CC_collection_dump_9418.json 2>> calc-log.csv 1>> pricing-out.csv`

# Shipping computation

## Introduction

A "Schedule of Rates" for shipping will be recorded within the CMS. These will be computed outside the CMS and updated as necessary (e.g. quarterly). Shipping costs depend on the product origin:

  * Domestic shipping is based on volume. It attracts no duty or other import costs.
  * International shipping depends on product weight or volume.

International duties and import costs are comprised of:

* Customs and Quarantine
  * Import declaration
  * Lodgement fees
  * Assessment fees
  * Customs declaration
  * Container Fees
  * Goods inspection (wood / no wood)
  * Fumigation (per m3 - not sure if this applies only if wood)
  * Cartage (per m3)

* Australian Destination Charges
  * APCA
  * Delivery order fee
  * CMR compliance
  * Port Licence Fee
  * Australian Customs Charges

* Agency and attendance charges
  * Quarantine compliance
  * ICS Processing Fee

* Australian Delivery Charges
  * Delivery (fixed and W/M)
  * Fuel surcharge (%)
  * Tailgate truck requirement.

Assume all prices are in AUD (this will be converted before input).

Note that some items are per shipment (e.g. Assessment fees) whereas others are per m3 so depend on volume of the item (e.g. fumigation).

Also, some items are dependent on the inclusion of wood in the product. e.g. Goods inspection and Fumigation?

*Some products ship in multiple boxes. For shipping dimensions it would be ideal if there was a way to add multiple dimensions. This would be separate from product dimensions used on the website.*

Note that a component of shipping costs depend on the "port" these are shipped out of therefore for each port the applicable shipper and associated costs should be captured in the CMS.

To work out the appropriate international shipping costs Sea LCL and Air price are computed and the lower is selected. Ideally both should be shown and the selected cost indicated.

In calculations of shipping costs based on volume a 15% increase in size or weight is applied to account for packaging.

International shipping also include a local delivery fee since the shippers charge a varying amount.

Import and quarantine for international shipping costs include fumigation when the product incorporates wood and the price of that depends on volume.

## Variable Definitions

### Suffixes

```
M: Metres
KG: Kilograms
MT: Metric Tonne
M3: Cubic Metres
WV: Weight or Volume (whichever is higher)
CW: Chargeable Weight (used in air freight)
Pct: Percent
```

### Item based inputs

```
ShippingPackagingAdjustmentPct = % increase to deal with packaging (eg 15%)
ShippingPackagingAdjustment = 1+(ShippingPackagingAdjustmentPct/100)
ItemWeightKG: Item's unpackaged weight in kilograms
ItemVolumeM3 = Item Length Mtr * Item Width Mtr * Item Height Mtr
MinimumOrder: Item's minimum order size

ShippedItemWeightKG = (ItemWeightKG * MinimumOrder) * ShippingPackagingAdjustment

ShippedItemWeightMT = ShippedItemWeightKG / 1000

ShippedItemVolumeM3 = ItemVolumeM3 * MinimumOrder * ShippingPackagingAdjustment

ShippedItemWV = MAX(ShippedItemWeightMT, ShippedItemVolumeM3)

ShippedItemVolumetricWeight = ShippedItemVolumeM3 * 167 / 1000

ShippedItemCW = MAX(ShippedItemVolumetricWeight, ShippedItemWeightMT)
```

### Per port International LCL

```
ShippingLCL_Collection_Min
  * LCL Collection Minimum (eg. C21)

ShippingLCL_Collection_MT
  * LCL Collection by MT (eg. C22)

ShippingLCL_Collection = MAX(
  ShippingLCL_Collection_Min,
  ShippingLCL_Collection_MT * ShippedItemWeightMT
)

ShippingLCLPerItem
  * LCL Shipping - Handling
  * Customs and Quarantine
    * Container Fees (all others same for AF too)
  * Australian Destination Charges - LCL
    * Delivery Order Fee (actually per B/L but we estimate)
    * CMR Compliance
    * Port License Fee (when B/L based)
  * Australian Customs Charges
    * Agency and attendance charges
    * Quarantine Compliance
    * ICS Processing Fee

ShippingLCL_Delivery_Min
  * LCL Collection Minimum (eg. C21)

ShippingLCL_Delivery_WV
  * LCL Collection by Weight or Volume (eg. C22)

ShippingLCL_DeliverySurchargePct
   Australian Delivery Charges - Fuel Surcharge Pct (eg 14%)

ShippingLCL_DeliverySurcharge = 1+(ShippingLCL_DeliverySurchargePct/100)

ShippingLCL_DeliveryTailgateTruck
  * Australian Delivery Charges - If item requires tailgate truck

ShippingLCL_Delivery = MAX(
  ShippingLCL_Delivery_Min,
  ShippingLCL_Delivery_WV * ShippedItemWV
) * ShippingLCL_DeliverySurcharge
+ IF (item requires a tailgate truck) THEN
  ShippingLCL_DeliveryTailgateTruck
ELSE
  0
END IF

ShippingLCLPerWV
  * LCL Shipping
    * Pier Pass
    * Ocean freight
    * BAF/EFAF
  * Australian Destination Charges
    * APCA
    * Port License Fee (when WV based)

ShippingLCLTotal =
  ShippingLCLPerItem +
  ShippingLCL_Collection +
  ShippingLCL_Delivery +
  ShippingLCLPerWV * ShippedItemWV
```

### Per port International AirFreight (AF)

```
ShippingAF_Collection_Min
  * AF Collection Minimum (eg. C21)

ShippingAF_Collection_CW
  * AF Collection by CW (eg. C22)

ShippingAF_Collection = MAX(
  ShippingAF_Collection_Min,
  ShippingAF_Collection_MT * ShippedItemCW
)

ShippingAFPerItem
  * AF Shipping
    * Airway Bill Fees
    * Security Fee (where fixed)
  * Australian Destination Charges - AF
    * IDF
    * CMR Compliance
  * Australian Customs Charges
    * Agency and attendance charges
    * Quarantine Compliance
    * ICS Processing Fee

ShippingAF_THC_Min
  * AirFreight THC Minimum

ShippingAF_THC_CW
  * AirFreight THC per CW

ShippingAF_THC = MAX(
  ShippingAF_THC_Min,
  ShippingAF_THC_CW * ShippedItemCW
)

ShippingAF_WarRisk_Min
  * AirFreight War Risk Minimum

ShippingAF_WarRisk_CW
  * AirFreight War Risk per CW

ShippingAF_WarRisk = MAX(
  ShippingAF_WarRisk_Min,
  ShippingAF_WarRisk_CW * ShippedItemCW
)

ShippingAF_Security_CW
  * AirFreight Security where per CW

ShippingAF_Security = ShippingAF_Security_CW * ShippedItemCW

ShippingAF_Freight_Min
  * AirFreight Freight Minimum

ShippingAF_Freight_CW
  * AirFreight Freight per CW

ShippingAF_Freight = MAX(
  ShippingAF_Freight_Min,
  ShippingAF_Freight_CW * ShippedItemCW
)

ShippingAF_Fuel_Min
  * AirFreight Fuel Surcharge Minimum

ShippingAF_Fuel_CW
  * AirFreight Fuel Surcharge per CW

ShippingAF_Fuel = MAX(
  ShippingAF_Fuel_Min,
  ShippingAF_Fuel_CW * ShippedItemCW
)

ShippingAF_ITF_Min
  * Australian Destination Charges - ITF Min

ShippingAF_ITF_MT
  * Australian Destination Charges - ITF per MT

ShippingAF_ITF = MAX(
  ShippingAF_ITF_Min,
  ShippingAF_ITF_MT * ShippedItemWeightMT
)

ShippingAF_Handling_Min
  * Australian Destination Charges - Airline Handling Minimum

ShippingAF_Handling_MT
  * Australian Destination Charges - Airline Handling Per MT

ShippingAF_Handling = MAX(
  ShippingAF_Handling_Min,
  ShippingAF_Handling_MT * ShippedItemWeightMT
)

ShippingAF_Delivery_Min
  * LCL Collection Minimum (eg. C21)

ShippingAF_Delivery_WV
  * LCL Collection by Weight or Volume (eg. C22)

ShippingAF_DeliverySurchargePct
   Australian Delivery Charges - Fuel Surcharge Pct (eg 14%)

ShippingAF_DeliverySurcharge = 1+(ShippingAF_DeliverySurchargePct/100)

ShippingAF_DeliveryTailgateTruck
  * Australian Delivery Charges - If item requires tailgate truck

ShippingAF_Delivery = MAX(
  ShippingAF_Delivery_Min,
  ShippingAF_Delivery_WV * ShippedItemWV
) * ShippingAF_DeliverySurcharge
+ IF (item requires a tailgate truck) THEN
  ShippingAF_DeliveryTailgateTruck
ELSE
  0
END IF

ShippingAFTotal =
  ShippingAFPerItem +
  ShippingAF_Collection +
  ShippingAF_THC +
  ShippingAF_WarRisk +
  ShippingAF_Security +
  ShippingAF_Freight +
  ShippingAF_Fuel +
  ShippingAF_ITF +
  ShippingAF_Handling +
  ShippingAF_Delivery
```

### Domestic Freight

```
ShippingDomesticCollectionMin
  * Minimum charge for collection
ShippingDomesticCollectionPerM3
  * Collection cost per M3

ShippingDomesticCollection = MAX (
  ShippingDomesticCollectionMin,
  ShippingDomesticCollectionPerM3 * ShippedItemVolumeM3
)

ShippingDomesticDelivery
  * Delivery Fee

ShippingDomesticDeliverySurchargePct
   Delivery Charges - Fuel Levy Pct (eg 14%)

ShippingDomesticDeliverySurcharge = 1+(ShippingDomesticDeliverySurchargePct/100)

ShippingDomesticTotal =
  ShippingDomesticDelivery +
  ShippingDomesticCollection * ShippingDomesticDeliverySurcharge
```

### Shipping Port Total

```
This is the minimum cost of shipping comparing LCL and Air Freight

ShippingPortTotal = IF ( International ) THEN
  MIN(ShippingLCLTotal, ShippingAFTotal)
ELSE
  ShippingDomesticTotal
END IF  
```

### Same for all international ports

```
CustomsQuarantinePerItem
  * Import Declaration
  * Lodgement Fees
  * Assessment Fees
  * Customs Declaration (Note question to Amon)

  Note that Container Fees don't apply to AF - see LCL

CustomsQuarantineInspectionNoWood
  * Cost of inspection when the item contains no wood

CustomsQuarantineInspectionWoodMin
  * Min cost of inspection when the item contains wood

CustomsQuarantineInspectionWoodPerM3
  * Cost of inspection per M3 when the item contains wood
  * Comprises Fumigation + Cartage

CustomsQuarantineInspectionWood =
  MAX(
    CustomsQuarantineInspectionWoodMin,
    CustomsQuarantineInspectionWoodPerM3 * ShippedItemVolumeM3
  )

CustomsQuarantineInspection =
  IF (item contains wood) THEN
    CustomsQuarantineInspectionWood
  ELSE
    CustomsQuarantineInspectionNoWood
  END IF

CustomsQuarantineTotal =
  CustomsQuarantinePerItem +
  CustomsQuarantineInspection
```

### Shipping Total

```
ShippingTotal =
  ShippingPortTotal +
  IF (International) THEN
    CustomsQuarantineTotal
  ELSE
    0
  END IF
```
