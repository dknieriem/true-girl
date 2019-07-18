import React from 'react'
import ReactDOM from 'react-dom'
import { PricingCurrencyDisplayStyle } from './pricing-currency-display-style'

function SettingsPricing() {
   return <>{ReactDOM.createPortal(<PricingCurrencyDisplayStyle />, document.getElementById('wps-settings-currency-display-style'))}</>
}

export { SettingsPricing }
