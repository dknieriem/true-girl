import React, { useContext, useState } from 'react'
import { SettingsContext } from '../_state/context'
import { FormToggle } from '@wordpress/components'
import { toBoolean } from '../../utils/utils'

function CartEnableNotes() {
   const [settingsState, settingsDispatch] = useContext(SettingsContext)
   const [isChecked, setIsChecked] = useState(toBoolean(settingsState.enableCartNotes))

   function onChange() {
      setIsChecked(!isChecked)
      settingsDispatch({ type: 'UPDATE_SETTING', payload: { key: 'enableCartNotes', value: !isChecked } })
   }

   return <FormToggle checked={isChecked} onChange={onChange} />
}

export { CartEnableNotes }
