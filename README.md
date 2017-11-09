# magento-LCB_Discount

Module for generating discount coupon codes

# Basic usage

```php
    /**
     * @return string
     */
    public function discountDirective(){

        $discountCode = '';

        if(Mage::helper('core')->isModuleEnabled('LCB_Discount')){
            $discountCode = Mage::helper('discount')->getCouponCode([
                'discount'=>10
            ]);
        }

        return $discountCode;
    }

```
