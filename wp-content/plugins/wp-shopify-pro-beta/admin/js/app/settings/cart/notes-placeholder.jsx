import React, { useContext, useState } from 'react'
import { SettingsContext } from '../_state/context'
import { TextareaControl } from '@wordpress/components'

function CartNotesPlaceholder() {
   const [settingsState, settingsDispatch] = useContext(SettingsContext)
   const [placeholderValue, setPlaceholderValue] = useState(settingsState.cartNotesPlaceholder)

   function onChange(text) {
      setPlaceholderValue(text)
      settingsDispatch({ type: 'UPDATE_SETTING', payload: { key: 'cartNotesPlaceholder', value: text } })
   }

   return <TextareaControl disabled={!settingsState.enableCartNotes} cols='60' value={placeholderValue} onChange={onChange} />
}

export { CartNotesPlaceholder }
