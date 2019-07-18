import React, { useContext, useState } from 'react'
import { SettingsContext } from '../_state/context'
import { TextareaControl } from '@wordpress/components'

function CartTerms() {
   const [settingsState, settingsDispatch] = useContext(SettingsContext)
   const [termsValue, setTermsValue] = useState(settingsState.cartTerms)

   function onChange(text) {
      setTermsValue(text)
      settingsDispatch({ type: 'UPDATE_SETTING', payload: { key: 'cartTerms', value: text } })
   }

   return <TextareaControl disabled={!settingsState.enableCartTerms} cols='60' value={termsValue} onChange={onChange} />
}

export { CartTerms }
