import React, { useContext, useState, useEffect, useRef } from 'react'
import { SettingsContext } from '../_state/context'
import { RadioControl } from '@wordpress/components'
import { toBoolean } from '../../utils/utils'

function PricingCurrencyDisplayStyle() {
   const [settingsState, settingsDispatch] = useContext(SettingsContext)
   const [selectedOption, setSelectedOption] = useState(settingsState.pricingCurrencyDisplayStyle)
   const [isDisabled, setIsDisabled] = useState(toBoolean(settingsState.isLiteSync))
   const isFirstRender = useRef(true)

   function onChange(value) {

      setSelectedOption(value)
      settingsDispatch({ type: 'UPDATE_SETTING', payload: { key: 'pricingCurrencyDisplayStyle', value: value } })
   }

   return (
      <div>
         <RadioControl
            selected={selectedOption}
            options={[
               {
                  label: 'Symbol (example: $19.99)',
                  value: 'symbol'
               },
               {
                  label: 'Code (example: USD 19.99)',
                  value: 'code'
               },
               {
                  label: 'Name (example: 19.99 US dollars)',
                  value: 'name'
               }
            ]}
            onChange={onChange}
         />
      </div>
   )
}

export { PricingCurrencyDisplayStyle }
