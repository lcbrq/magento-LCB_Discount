<?php

/**
 * Class LCB_Discount_Helper_Data
 * @author Marcin Jigsaw Gierus
 */
class LCB_Discount_Helper_Data extends Mage_Core_Helper_Abstract
{

    /**
     * Check if particular rule exists
     *
     * @param $ruleName
     * @param array $params
     * @return string
     */
    public function getCouponCode($ruleName, $params = [])
    {
        $couponCode ='';

        if (Mage::getStoreConfig('general/coupon_code/enable_disable')) {
            $couponCode = null;
            $rules = Mage::getModel('salesrule/rule')->getCollection()
                ->addFieldToFilter('name', array('in' => $ruleName));

            if ($rules->getSize()) {
                $ruleId = $rules->getFirstItem()->getId();
                $couponCode = $this->generateCouponCode($ruleId);
            } else {
                $couponCode = $this->createShoppingCartRule($ruleName,$params);
            }

        }

        return $couponCode;
    }

    /**
     * Generate coupon code for given rule
     *
     * @param int $ruleId
     * @return mixed
     */
    public function generateCouponCode($ruleId)
    {
        $rule = Mage::getModel('salesrule/rule')->load($ruleId);

        $generator = Mage::getModel('salesrule/coupon_massgenerator');

        $generator->setFormat(Mage_SalesRule_Helper_Coupon::COUPON_FORMAT_ALPHANUMERIC);;

        $generator->setDash('');
        $generator->setLength(10);

        $rule->setCouponCodeGenerator($generator);
        $rule->setCouponType(Mage_SalesRule_Model_Rule::COUPON_TYPE_AUTO);

        $coupon = $rule->acquireCoupon();
        $coupon->setUsageLimit(1);
        $coupon->setTimesUsed(0);
        $coupon->setType(1);
        $coupon->save();
        $code = $coupon->getCode();

        return $code;
    }

    /**
     * Create new rule
     *
     * @param  string $ruleName
     * @param array $params
     * @return mixed
     */
    public function createShoppingCartRule($ruleName,$params = [])
    {
        $actionType = Mage_SalesRule_Model_Rule::BY_PERCENT_ACTION;
        if(isset($params['discount'])){
            $discount = $params['discount'];
        }else{
            $discount = 5;
        }
        $groupIds = [0]; //0 is non-logged group

        $customerGroups = Mage::helper('customer')->getGroups();
        foreach ($customerGroups as $group) {
            array_push($groupIds, $group->getId());
        }

        $websitesIds = explode(',', Mage::getStoreConfig('general/coupon_code/discount_website'));

        $shoppingCartPriceRule = Mage::getModel('salesrule/rule');

        $shoppingCartPriceRule
            ->setName($ruleName)
            ->setDescription($ruleName)
            ->setIsActive(1)
            ->setWebsiteIds($websitesIds)
            ->setSimpleAction($actionType)
            ->setDiscountAmount($discount)
            ->setCouponType('2')
            ->setUseAutoGeneration('1')
            ->setCustomerGroupIds($groupIds)
            ->setStopRulesProcessing(0);

        $shoppingCartPriceRule->save();
        $id = $shoppingCartPriceRule->getId();

        //After the shipping cart rule is created also generate coupon code for it
        return $this->generateCouponCode($id);
    }
}
	 